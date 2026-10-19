<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class KernelRunnerService
{
    public function __construct(private SandboxService $sandbox, private WorkspaceService $workspace) {}

    /** @return array{executable: string, version: string}|null */
    public function pythonRuntime(): ?array
    {
        foreach (['python3', 'python'] as $name) {
            $path = (new ExecutableFinder)->find($name);
            if (! $path) {
                continue;
            }
            $probe = new Process([$path, '--version']);
            $probe->setTimeout(5);
            if ($probe->run() === 0 && str_starts_with($version = trim($probe->getOutput()), 'Python 3.')) {
                return ['executable' => realpath($path) ?: $path, 'version' => $version];
            }
        }

        return null;
    }

    /** @return array<string, array{available: bool, version: string}> */
    public function detectKernels(): array
    {
        $available = $this->sandbox->available();
        $kernels = [];
        foreach (['python' => ['/usr/bin/python3', '--version'], 'php' => ['/usr/bin/php', '-v'], 'node' => ['/usr/bin/node', '--version']] as $name => $command) {
            $version = $available ? $this->sandbox->run($command) : null;
            $kernels[$name] = ['available' => $version !== null && $version['exitCode'] === 0, 'version' => $version ? (strtok($version['stdout'], "\n") ?: 'Not detected') : 'Execution unavailable'];
        }

        return $kernels;
    }

    /** @return array<string, mixed> */
    public function run(string $code, string $language, string $filePath, string $basePath): array
    {
        if ($filePath !== '') {
            $this->workspace->resolve($filePath);
        }
        if ($code === '' && $filePath !== '') {
            $file = $this->workspace->resolve($filePath);
            abort_unless(is_file($file), 404);
            $code = file_get_contents($file);
            abort_unless($code !== false, 422, 'Could not read the file.');
        }
        $language = strtolower($language ?: pathinfo($filePath, PATHINFO_EXTENSION));
        $runtime = match ($language) {
            'py', 'python' => ['/usr/bin/python3', '-u', '/run/code'],
            'php' => ['/usr/bin/php', '/run/code'],
            'js', 'javascript', 'node' => ['/usr/bin/node', '--max-old-space-size=128', '--jitless', '/run/code'],
            'sh', 'shell', 'bash' => ['/usr/bin/bash', '/run/code'],
            default => abort(422, 'This language is not supported by the code runner.'),
        };
        abort_if(trim($code) === '', 422, 'No code provided.');
        $this->sandbox->requireAvailable();
        $directory = storage_path('app/kernel_runner');
        File::ensureDirectoryExists($directory, 0700);
        $file = $directory.'/'.bin2hex(random_bytes(24));
        file_put_contents($file, $code);
        try {
            return ['ok' => true, 'kernel' => $language, 'language' => $language] + $this->sandbox->run($runtime, '', $file);
        } finally {
            unlink($file);
        }
    }
}
