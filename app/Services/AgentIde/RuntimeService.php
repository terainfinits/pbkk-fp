<?php

namespace App\Services\AgentIde;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class RuntimeService
{
    /** @var array{command: list<string>, executable: string, version: string}|null */
    private ?array $python = null;

    public function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    protected function locate(string $name): ?string
    {
        if (is_file($name) && is_executable($name)) {
            return $name;
        }

        return (new ExecutableFinder)->find($name);
    }

    /** @param list<string> $command */
    protected function probe(array $command): ?string
    {
        try {
            $process = new Process([...$command, '--version']);
            $process->setTimeout(5);
            if ($process->run() === 0) {
                return trim($process->getOutput().$process->getErrorOutput());
            }
        } catch (\Throwable) {
        }

        return null;
    }

    /** @return array{command: list<string>, executable: string, version: string}|null */
    public function python(): ?array
    {
        if ($this->python !== null) {
            return $this->python;
        }
        $configured = config('ide.python_binary');
        $candidates = $configured ? [[$configured]] : ($this->isWindows()
            ? [['python'], ['py', '-3'], ['python3']] : [['python3'], ['python']]);
        foreach ($candidates as $candidate) {
            if (! $executable = $this->locate($candidate[0])) {
                continue;
            }
            $command = [$executable, ...array_slice($candidate, 1)];
            $version = $this->probe($command);
            if ($version !== null && preg_match('/^Python 3\./', $version)) {
                return $this->python = ['command' => $command, 'executable' => $executable, 'version' => $version];
            }
        }

        return null;
    }

    /** @return list<string>|null */
    public function find(string $runtime): ?array
    {
        if ($runtime === 'python') {
            return $this->python()['command'] ?? null;
        }
        $configured = config('ide.'.$runtime.'_binary');
        $candidates = $configured ? [$configured] : match ($runtime) {
            'php' => [(new PhpExecutableFinder)->find(false) ?: 'php', 'php'],
            'node' => ['node', 'nodejs'],
            'shell' => $this->isWindows() ? ['pwsh', 'powershell'] : ['bash'],
            default => [],
        };
        foreach ($candidates as $name) {
            if ($executable = $this->locate($name)) {
                return [$executable];
            }
        }

        return null;
    }

    /** @return list<string> */
    public function require(string $runtime): array
    {
        $command = $this->find($runtime);
        abort_unless($command !== null, 422, "The {$runtime} runtime was not found. Install it or configure its executable path in .env.");

        return $command;
    }
}
