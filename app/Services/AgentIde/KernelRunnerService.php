<?php

namespace App\Services\AgentIde;

/**
 * Detects available runtime kernels (Python/PHP/Node) and executes
 * arbitrary code snippets against them in a temporary script file.
 */
class KernelRunnerService
{
    private const SUPPORTED = [
        'python' => ['py', 'python3'],
        'py' => ['py', 'python3'],
        'javascript' => ['js', null],
        'js' => ['js', null],
        'node' => ['js', null],
        'typescript' => ['js', null],
        'ts' => ['js', null],
        'php' => ['php', null],
        'shell' => ['ps1', null],
        'sh' => ['ps1', null],
        'bash' => ['ps1', null],
        'powershell' => ['ps1', null],
        'ps1' => ['ps1', null],
        'bat' => ['ps1', null],
    ];

    public function detectKernels(): array
    {
        $pythonVersion = $this->execVersion('python --version') ?: $this->execVersion('python3 --version');
        $phpVersion = $this->execVersion('php -v');
        if ($phpVersion && preg_match('/PHP\s+([0-9.]+)/i', $phpVersion, $m)) {
            $phpVersion = 'PHP ' . $m[1];
        }
        $nodeVersion = $this->execVersion('node -v');

        return [
            'python' => [
                'name' => 'Python 3 Kernel',
                'available' => !empty($pythonVersion),
                'version' => $pythonVersion ?: 'Not Detected',
                'executable' => 'python',
            ],
            'php' => [
                'name' => 'PHP Kernel',
                'available' => !empty($phpVersion),
                'version' => $phpVersion ?: 'PHP ' . PHP_VERSION,
                'executable' => 'php',
            ],
            'node' => [
                'name' => 'Node.js Kernel',
                'available' => !empty($nodeVersion),
                'version' => $nodeVersion ? 'Node ' . trim($nodeVersion) : 'Not Detected',
                'executable' => 'node',
            ],
        ];
    }

    private function execVersion(string $command): ?string
    {
        try {
            $output = [];
            $returnVar = 1;
            exec($command . ' 2>&1', $output, $returnVar);
            if ($returnVar === 0 && !empty($output)) {
                return trim($output[0]);
            }
        } catch (\Throwable) {
            // Ignore — kernel simply isn't available.
        }

        return null;
    }

    /**
     * Run a code snippet (or a file on disk when $code is empty) in the
     * matching kernel and return its stdout/stderr/exit code.
     */
    public function run(string $code, string $language, string $filePath, string $basePath): array
    {
        if (empty($code) && !empty($filePath)) {
            $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . $filePath);
            if ($fullPath && str_starts_with($fullPath, $basePath) && is_file($fullPath)) {
                $code = file_get_contents($fullPath);
                $language = $language ?: pathinfo($fullPath, PATHINFO_EXTENSION);
            }
        }

        if (empty(trim($code))) {
            return ['ok' => false, 'error' => 'No code provided to execute.'];
        }

        [$executable, $ext, $kernelName] = $this->resolveRuntime(strtolower($language));

        $tempDir = storage_path('app/kernel_runner');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'run_' . uniqid() . '.' . $ext;
        file_put_contents($tempFile, $code);

        $startTime = microtime(true);
        $cmd = $executable === 'powershell'
            ? 'powershell -ExecutionPolicy Bypass -File ' . escapeshellarg($tempFile)
            : escapeshellcmd($executable) . ' ' . escapeshellarg($tempFile);

        [$stdout, $stderr, $exitCode] = $this->execProcess($cmd, $basePath);
        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }

        return [
            'ok' => true,
            'kernel' => $kernelName,
            'executable' => $executable,
            'language' => $language ?: $ext,
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exitCode' => $exitCode,
            'executionTimeMs' => $executionTimeMs,
        ];
    }

    private function resolveRuntime(string $language): array
    {
        return match (true) {
            in_array($language, ['python', 'py']) => ['python', 'py', 'Python 3 Kernel'],
            in_array($language, ['javascript', 'js', 'node', 'typescript', 'ts']) => ['node', 'js', 'Node.js Kernel'],
            in_array($language, ['shell', 'sh', 'bash', 'powershell', 'ps1', 'bat']) => ['powershell', 'ps1', 'Powershell Kernel'],
            default => ['php', 'php', 'PHP Kernel'],
        };
    }

    private function execProcess(string $cmd, string $cwd): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptorSpec, $pipes, $cwd);

        $stdout = '';
        $stderr = '';
        $exitCode = -1;

        if (is_resource($process)) {
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
        }

        return [$stdout ?: '', $stderr ?: '', $exitCode];
    }
}
