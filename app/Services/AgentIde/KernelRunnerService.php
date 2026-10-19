<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\File;

class KernelRunnerService
{
    public function __construct(private SandboxService $sandbox, private WorkspaceService $workspace, private RuntimeService $runtimes) {}

    /** @return array{command: list<string>, executable: string, version: string}|null */
    public function pythonRuntime(): ?array
    {
        return $this->runtimes->python();
    }

    /** @return array<string, array{available: bool, version: string}> */
    public function detectKernels(): array
    {
        $available = $this->sandbox->available();
        $kernels = [];
        foreach (['python', 'php', 'node'] as $name) {
            try {
                $version = $available ? $this->sandbox->run([...$this->sandbox->runtime($name), '--version']) : null;
            } catch (\Throwable) {
                $version = null;
            }
            $kernels[$name] = ['available' => $version !== null && $version['exitCode'] === 0, 'version' => $version ? (strtok($version['stdout'], "\n") ?: 'Not detected') : 'Execution unavailable'];
        }

        return $kernels;
    }

    /** @return array<string, mixed> */
    public function run(string $code, string $language, string $filePath, string $basePath): array
    {
        $this->sandbox->requireAvailable();
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
            'py', 'python' => [...$this->sandbox->runtime('python'), '-u', '/run/code'],
            'php' => [...$this->sandbox->runtime('php'), '/run/code'],
            'js', 'javascript', 'node' => [...$this->sandbox->runtime('node'), '--max-old-space-size=128', '/run/code'],
            'sh', 'shell', 'bash', 'powershell', 'ps1' => [...$this->sandbox->runtime('shell'), ...($this->sandbox->shellInfo()['platform'] === 'windows' ? ['-NoLogo', '-NoProfile', '-File'] : []), '/run/code'],
            default => abort(422, 'This language is not supported by the code runner.'),
        };
        abort_if(trim($code) === '', 422, 'No code provided.');
        $directory = storage_path('app/kernel_runner');
        File::ensureDirectoryExists($directory, 0700);
        $file = $directory.'/'.bin2hex(random_bytes(24)).match ($language) {
            'py', 'python' => '.py', 'php' => '.php', 'js', 'javascript', 'node' => '.js', 'ps1', 'powershell' => '.ps1', default => $this->sandbox->shellInfo()['platform'] === 'windows' ? '.ps1' : '.sh'
        };
        file_put_contents($file, $code);
        try {
            return ['ok' => true, 'kernel' => $language, 'language' => $language] + $this->sandbox->run($runtime, '', $file);
        } finally {
            unlink($file);
        }
    }
}
