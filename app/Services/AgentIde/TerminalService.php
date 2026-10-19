<?php

namespace App\Services\AgentIde;

class TerminalService
{
    public function __construct(private SandboxService $sandbox, private WorkspaceService $workspace) {}

    /** @return array{stdout: string, stderr: string, exitCode: int, executionTimeMs: int, cwd: string} */
    public function execute(string $command, string $cwd = ''): array
    {
        $this->sandbox->requireAvailable();
        $cwd = $this->workspace->normalize($cwd, true);
        $this->workspace->resolve($cwd, true);
        if (preg_match('/^cd(?:\s+(.*))?$/', trim($command), $match) && ! preg_match('/[;&|$`]/', $match[1] ?? '')) {
            $target = trim($match[1] ?? '', " \t\n\r\0\x0B\"'");
            $parts = $cwd === '' ? [] : explode('/', $cwd);
            if ($target === '' || $target === '~' || $target === '/') {
                $parts = [];
            } else {
                foreach (explode('/', str_replace('\\', '/', $target)) as $part) {
                    if ($part === '..') {
                        abort_unless(count($parts) > 0, 422, 'Cannot leave your workspace.');
                        array_pop($parts);
                    } elseif ($part !== '' && $part !== '.') {
                        $parts[] = $part;
                    }
                }
            }
            $cwd = implode('/', $parts);
            abort_unless(is_dir($this->workspace->resolve($cwd, true)), 422, 'Directory not found.');

            return ['stdout' => '', 'stderr' => '', 'exitCode' => 0, 'executionTimeMs' => 0, 'cwd' => $cwd];
        }

        return $this->sandbox->run(['/usr/bin/bash', '-c', $command], $cwd) + ['cwd' => $cwd];
    }
}
