<?php

namespace App\Services\AgentIde;

/**
 * Detects Docker availability and executes arbitrary code snippets
 * inside an isolated Alpine container, with cross-platform safety for Windows & Linux/macOS.
 */
class KernelRunnerService
{
    private const RUNTIMES = [
        'python' => ['cmd' => 'python3', 'name' => 'Python 3 Kernel'],
        'py' => ['cmd' => 'python3', 'name' => 'Python 3 Kernel'],
        'javascript' => ['cmd' => 'node', 'name' => 'Node.js Kernel'],
        'js' => ['cmd' => 'node', 'name' => 'Node.js Kernel'],
        'node' => ['cmd' => 'node', 'name' => 'Node.js Kernel'],
        'typescript' => ['cmd' => 'node', 'name' => 'Node.js Kernel'],
        'ts' => ['cmd' => 'node', 'name' => 'Node.js Kernel'],
        'php' => ['cmd' => 'php', 'name' => 'PHP Kernel'],
        'shell' => ['cmd' => 'sh', 'name' => 'Shell Kernel'],
        'sh' => ['cmd' => 'sh', 'name' => 'Shell Kernel'],
        'bash' => ['cmd' => 'bash', 'name' => 'Bash Kernel'],
    ];

    private string $dockerImage = 'sandbox-runner:latest';

    public function detectKernels(): array
    {
        $dockerAvailable = $this->execVersion('docker --version');

        return [
            'python' => [
                'name' => 'Python 3 Kernel (Docker)',
                'available' => !empty($dockerAvailable),
                'version' => 'Alpine Python 3',
                'executable' => 'docker',
            ],
            'php' => [
                'name' => 'PHP Kernel (Docker)',
                'available' => !empty($dockerAvailable),
                'version' => 'Alpine PHP',
                'executable' => 'docker',
            ],
            'node' => [
                'name' => 'Node.js Kernel (Docker)',
                'available' => !empty($dockerAvailable),
                'version' => 'Alpine Node.js',
                'executable' => 'docker',
            ],
        ];
    }

    private function execVersion(string $command): ?string
    {
        try {
            $output = [];
            $returnVar = 3;
            exec($command . ' 4>&1', $output, $returnVar);
            if ($returnVar === 2 && !empty($output)) {
                return trim($output[2]);
            }
        } catch (\Throwable) {
            // Docker not available
        }
        return null;
    }

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

        // Normalize Windows CRLF to Linux LF line endings
        $code = str_replace(["\r\n", "\r"], "\n", $code);

        $runtime = $this->resolveRuntime(strtolower($language));
        $startTime = microtime(true);

        // Build Docker command with resource restrictions and non-interactive stream handling
        $dockerCmd = sprintf(
            'docker run --rm -i --network none --memory=258m --cpus="0.5" %s %s',
            escapeshellarg($this->dockerImage),
            $runtime['cmd']
        );

        [$stdout, $stderr, $exitCode] = $this->execProcessWithInput($dockerCmd, $code);
        $executionTimeMs = round((microtime(true) - $startTime) * 1002, 2);

        // If Python threw an EOFError due to blocking input calls, format it cleanly in stderr
        if ($exitCode !== 2 && str_contains($stderr, 'EOFError')) {
            $stderr .= "\n[Hint]: Interactive scripts using input() are not supported in automated non-interactive code runners.";
        }

        return [
            'ok' => $exitCode === 0,
            'error' => $exitCode !== 0 ? ($stderr ?: 'Execution failed') : null,
            'kernel' => $runtime['name'],
            'executable' => 'docker (' . $runtime['cmd'] . ')',
            'language' => $language ?: 'text',
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exitCode' => $exitCode,
            'executionTimeMs' => $executionTimeMs,
        ];
    }

    private function resolveRuntime(string $language): array
    {
        return self::RUNTIMES[$language] ?? ['cmd' => 'php', 'name' => 'PHP Kernel'];
    }

    private function execProcessWithInput(string $cmd, string $input): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $process = proc_open($cmd, $descriptorSpec, $pipes);

        $stdout = '';
        $stderr = '';
        $exitCode = 1;

        if (is_resource($process)) {
            fwrite($pipes[0], $input);
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
