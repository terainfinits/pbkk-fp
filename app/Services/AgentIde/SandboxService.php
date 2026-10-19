<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class SandboxService
{
    public function __construct(private WorkspaceService $workspace, private RuntimeService $runtimes) {}

    /**
     * @param  list<string>  $arguments
     * @return list<string>
     */
    public function command(array $arguments, string $cwd = '', ?string $script = null): array
    {
        $root = $this->workspace->root();
        $relative = $this->workspace->normalize($cwd, true);
        abort_unless(is_dir($this->workspace->resolve($relative, true)), 422, 'Working directory not found.');
        abort_unless(in_array(config('ide.sandbox_driver'), ['native', 'docker', 'bubblewrap'], true), 503, 'Unknown execution driver.');
        if ($this->usesNative()) {
            abort_unless(app()->environment('local', 'testing'), 503, 'Native execution is only available in local development. Select an isolated driver.');

            return array_map(fn (string $argument) => $argument === '/run/code' && $script !== null ? $script : $argument, $arguments);
        }
        if ($this->usesDocker()) {
            return $this->dockerCommand($arguments, $root, $relative, $script);
        }
        $binary = config('ide.sandbox_binary');
        abort_unless(PHP_OS_FAMILY === 'Linux' && is_executable($binary) && is_executable('/usr/bin/prlimit'), 503, 'Isolated execution is unavailable. Install Bubblewrap and util-linux on Linux, or use the Docker driver.');
        $command = [
            $binary, '--unshare-all', '--die-with-parent', '--new-session', '--cap-drop', 'ALL',
            '--uid', (string) posix_geteuid(), '--gid', (string) posix_getegid(),
            '--ro-bind', '/usr', '/usr', '--symlink', 'usr/bin', '/bin',
            '--symlink', 'usr/lib', '/lib', '--symlink', 'usr/lib64', '/lib64',
            '--proc', '/proc', '--dev', '/dev', '--tmpfs', '/tmp', '--dir', '/run',
            '--bind', $root, '/workspace', '--chdir', '/workspace'.($relative ? '/'.$relative : ''),
            '--clearenv', '--setenv', 'PATH', '/usr/bin:/bin', '--setenv', 'HOME', '/tmp',
            '--setenv', 'PYTHONIOENCODING', 'utf-8', '--setenv', 'LANG', 'C.UTF-8',
            '--setenv', 'PYTHONPATH', '/workspace',
        ];
        // Debian's /usr/bin/php points through this individual alternatives link.
        if (($php = realpath('/etc/alternatives/php')) !== false) {
            array_push($command, '--ro-bind', $php, '/etc/alternatives/php');
        }
        if ($script) {
            array_push($command, '--ro-bind', $script, '/run/code');
        }

        return [...$command, '--', '/usr/bin/prlimit', '--as=1073741824', '--cpu=30', '--nproc=128', '--nofile=128', '--fsize=16777216', '--', ...$arguments];
    }

    public function usesDocker(): bool
    {
        return config('ide.sandbox_driver') === 'docker';
    }

    public function usesNative(): bool
    {
        return config('ide.sandbox_driver') === 'native';
    }

    /** @return list<string> */
    public function runtime(string $runtime): array
    {
        if ($this->usesNative()) {
            return $this->runtimes->require($runtime);
        }

        return [match ($runtime) {
            'python' => '/usr/bin/python3', 'php' => '/usr/bin/php',
            'node' => '/usr/bin/node', 'shell' => '/usr/bin/bash',
            default => abort(422, 'Unsupported runtime.'),
        }];
    }

    /** @return array{name: string, platform: string} */
    public function shellInfo(): array
    {
        $windows = $this->usesNative() && $this->runtimes->isWindows();

        return ['name' => $windows ? 'PowerShell' : 'Bash', 'platform' => $windows ? 'windows' : 'linux'];
    }

    /** @return list<string> */
    public function shellCommand(string $command): array
    {
        if ($this->shellInfo()['platform'] === 'windows') {
            $encoding = '[Console]::OutputEncoding = [System.Text.UTF8Encoding]::new($false); $OutputEncoding = [Console]::OutputEncoding; ';

            return [...$this->runtime('shell'), '-NoLogo', '-NoProfile', '-NonInteractive', '-Command', $encoding.$command];
        }

        return [...$this->runtime('shell'), '-c', $command];
    }

    /**
     * @param  list<string>  $arguments
     * @return list<string>
     */
    private function dockerCommand(array $arguments, string $root, string $cwd, ?string $script): array
    {
        $configured = config()->string('ide.docker_binary');
        $binary = is_file($configured) ? $configured : (new ExecutableFinder)->find($configured);
        abort_unless($binary !== null, 503, 'Docker Desktop is required for isolated execution on Windows.');
        abort_if(str_contains($root, ',') || ($script !== null && str_contains($script, ',')), 422, 'Docker workspace paths cannot contain commas.');
        $command = [
            $binary, 'run', '--rm', '--pull=never', '--init', '-i', '--name', 'magentic-'.bin2hex(random_bytes(16)),
            '--network=none', '--cap-drop=ALL', '--security-opt=no-new-privileges', '--read-only',
            '--memory=512m', '--memory-swap=512m', '--pids-limit=128', '--cpus=1',
            '--ulimit', 'nofile=128:128', '--ulimit', 'fsize=16777216:16777216', '--ulimit', 'cpu=30:30',
            '--user', PHP_OS_FAMILY === 'Linux' ? posix_geteuid().':'.posix_getegid() : '1000:1000',
            '--tmpfs', '/tmp:rw,nosuid,size=64m', '--workdir', '/workspace'.($cwd ? '/'.$cwd : ''),
            '--env', 'HOME=/tmp', '--env', 'PYTHONIOENCODING=utf-8', '--env', 'LANG=C.UTF-8',
            '--env', 'PYTHONPATH=/workspace',
            '--mount', 'type=bind,source='.$root.',target=/workspace',
        ];
        if ($script) {
            array_push($command, '--mount', 'type=bind,source='.$script.',target=/run/code,readonly');
        }

        return [...$command, config()->string('ide.docker_image'), ...$arguments];
    }

    /**
     * @param  list<string>  $command
     * @return list<string>
     */
    public function cleanupCommand(array $command): array
    {
        $position = array_search('--name', $command, true);
        if (($command[1] ?? '') !== 'run' || $position === false || ! str_starts_with($command[$position + 1], 'magentic-')) {
            return [];
        }

        return [$command[0], 'rm', '--force', $command[$position + 1]];
    }

    /** @param list<string> $command */
    public function cleanup(array $command): void
    {
        if ($cleanup = $this->cleanupCommand($command)) {
            try {
                (new Process($cleanup))->setTimeout(5)->run();
            } catch (\Throwable) {
            }
        }
    }

    public function available(): bool
    {
        if ($this->usesNative()) {
            return app()->environment('local', 'testing');
        }
        $command = [];
        try {
            $command = $this->command(['/usr/bin/true']);
            $probe = new Process($command);
            $probe->setTimeout(5);

            return $probe->run() === 0;
        } catch (\Throwable) {
            return false;
        } finally {
            $this->cleanup($command);
        }
    }

    public function requireAvailable(): void
    {
        abort_unless(in_array(config('ide.sandbox_driver'), ['native', 'docker', 'bubblewrap'], true), 503, 'Unknown execution driver.');
        abort_if($this->usesNative() && ! app()->environment('local', 'testing'), 503, 'Native execution is only available in local development. Select an isolated driver.');
        abort_unless($this->available(), 503, $this->usesDocker()
            ? 'Start Docker Desktop in Linux-container mode and build the Magentic runtime image. See README.md.'
            : 'Isolated execution is unavailable on this server. Ask the administrator to enable Bubblewrap user namespaces.');
    }

    /**
     * @param  list<string>  $arguments
     * @return array{stdout: string, stderr: string, exitCode: int, executionTimeMs: int}
     */
    public function run(array $arguments, string $cwd = '', ?string $script = null): array
    {
        $this->requireAvailable();
        $command = $this->command($arguments, $cwd, $script);
        $python = $this->runtimes->require('python');
        $directory = config('ide.runs_root').'/sync-'.bin2hex(random_bytes(16));
        File::ensureDirectoryExists($directory, 0700);
        File::put($directory.'/command.json', json_encode($command, JSON_THROW_ON_ERROR));
        File::put($directory.'/cleanup.json', json_encode($this->cleanupCommand($command), JSON_THROW_ON_ERROR));
        File::put($directory.'/limits.json', json_encode(['seconds' => 30, 'interactive' => false], JSON_THROW_ON_ERROR));
        File::put($directory.'/input.jsonl', '');
        $process = new Process([...$python, base_path('scripts/python_session.py'), '--worker', $directory, $this->workspace->resolve($cwd, true)]);
        $process->setTimeout(40);
        try {
            $process->mustRun();
            $state = json_decode(File::get($directory.'/state.json'), true, 512, JSON_THROW_ON_ERROR);

            return ['stdout' => $state['stdout'], 'stderr' => $state['stderr'], 'exitCode' => $state['exitCode'], 'executionTimeMs' => $state['executionTimeMs']];
        } finally {
            $this->cleanup($command);
            File::deleteDirectory($directory);
        }
    }
}
