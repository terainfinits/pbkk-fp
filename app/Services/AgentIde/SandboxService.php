<?php

namespace App\Services\AgentIde;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class SandboxService
{
    public function __construct(private WorkspaceService $workspace) {}

    /**
     * @param  list<string>  $arguments
     * @return list<string>
     */
    public function command(array $arguments, string $cwd = '', ?string $script = null): array
    {
        $binary = config('ide.sandbox_binary');
        abort_unless(PHP_OS_FAMILY === 'Linux' && is_executable($binary) && is_executable('/usr/bin/prlimit'), 503, 'Isolated execution is unavailable. Install Bubblewrap and util-linux on the server.');
        $root = $this->workspace->root();
        $relative = $this->workspace->normalize($cwd, true);
        abort_unless(is_dir($this->workspace->resolve($relative, true)), 422, 'Working directory not found.');
        $command = [
            $binary, '--unshare-all', '--die-with-parent', '--new-session', '--cap-drop', 'ALL',
            '--uid', (string) posix_geteuid(), '--gid', (string) posix_getegid(),
            '--ro-bind', '/usr', '/usr', '--symlink', 'usr/bin', '/bin',
            '--symlink', 'usr/lib', '/lib', '--symlink', 'usr/lib64', '/lib64',
            '--proc', '/proc', '--dev', '/dev', '--tmpfs', '/tmp', '--dir', '/run',
            '--bind', $root, '/workspace', '--chdir', '/workspace'.($relative ? '/'.$relative : ''),
            '--clearenv', '--setenv', 'PATH', '/usr/bin:/bin', '--setenv', 'HOME', '/tmp',
            '--setenv', 'PYTHONIOENCODING', 'utf-8', '--setenv', 'LANG', 'C.UTF-8',
        ];
        // Debian's /usr/bin/php points through this individual alternatives link.
        if (is_file('/etc/alternatives/php')) {
            array_push($command, '--ro-bind', realpath('/etc/alternatives/php'), '/etc/alternatives/php');
        }
        if ($script) {
            array_push($command, '--ro-bind', $script, '/run/code');
        }

        return [...$command, '--', '/usr/bin/prlimit', '--as=1073741824', '--cpu=30', '--nproc=128', '--nofile=128', '--fsize=16777216', '--', ...$arguments];
    }

    public function available(): bool
    {
        try {
            $probe = new Process($this->command(['/usr/bin/true']));
            $probe->setTimeout(5);

            return $probe->run() === 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public function requireAvailable(): void
    {
        abort_unless($this->available(), 503, 'Isolated execution is unavailable on this server. Ask the administrator to enable Bubblewrap user namespaces.');
    }

    /**
     * @param  list<string>  $arguments
     * @return array{stdout: string, stderr: string, exitCode: int, executionTimeMs: int}
     */
    public function run(array $arguments, string $cwd = '', ?string $script = null): array
    {
        $this->requireAvailable();
        $process = new Process($this->command($arguments, $cwd, $script), null, ['PATH' => '/usr/bin:/bin']);
        $process->setTimeout(30);
        $start = microtime(true);
        $stdout = '';
        $stderr = '';
        try {
            $process->run(function (string $type, string $output) use (&$stdout, &$stderr, $process) {
                $remaining = config()->integer('ide.output_limit') - strlen($stdout) - strlen($stderr);
                if ($type === Process::OUT) {
                    $stdout .= substr($output, 0, max(0, $remaining));
                } else {
                    $stderr .= substr($output, 0, max(0, $remaining));
                }
                $process->clearOutput();
                $process->clearErrorOutput();
                if (strlen($output) > $remaining) {
                    $process->stop(0);
                    $stderr .= "\nOutput limit reached.\n";
                }
            });
        } catch (ProcessTimedOutException) {
            $stderr .= "\nExecution timed out after 30 seconds.\n";
        }

        return ['stdout' => $stdout, 'stderr' => $stderr, 'exitCode' => $process->getExitCode() ?? -1, 'executionTimeMs' => (int) round((microtime(true) - $start) * 1000)];
    }
}
