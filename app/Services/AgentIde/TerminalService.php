<?php

namespace App\Services\AgentIde;

use App\Services\AgentIde\FileExplorerService;

/**
 * Executes arbitrary shell commands in a PowerShell environment.
 * Maintains a virtual current-working-directory per session (stored server-side via session).
 */
class TerminalService {
    /** Allowed base directory — commands cannot escape outside this path. */
    private string $basePath;

    public function __construct(private FileExplorerService $files)
    {
        $this->basePath = $this->files->basePath();
    }

    /**
     * Execute a shell command string and return the output.
     *
     * @param  string  $command   Raw command typed by the user.
     * @param  string  $cwd       Relative path from basePath (e.g. "app/Http").
     * @return array{ok:bool, stdout:string, stderr:string, exitCode:int, executionTimeMs:float, newCwd:string}
     */
    public function execute(string $command, string $cwd = ''): array
    {
        $command = trim($command);

        if (empty($command)) {
            return $this->result(true, '', '', 0, 0.0, $cwd);
        }

        // Resolve the absolute working directory (sandboxed inside basePath)
        $absoluteCwd = $this->resolveAbsoluteCwd($cwd);

        // Handle built-in `cd` command client-side in JS, but we still support
        // server-side resolution for relative paths sent with the command.
        // If user typed `cd something`, we update cwd and return early.
        if (preg_match('/^cd\s*(.*)/i', $command, $m)) {
            $target = trim($m[1]);
            $newCwd = $this->resolveNewCwd($absoluteCwd, $target);
            return $this->result(true, '', '', 0, 0.0, $this->toRelativeCwd($newCwd));
        }

        // For `cls` / `clear` — we handle client-side but send empty response.
        if (in_array(strtolower($command), ['cls', 'clear'])) {
            return $this->result(true, '__CLEAR__', '', 0, 0.0, $cwd);
        }

        $startTime = microtime(true);

        // Build PowerShell command: set location first, then execute.
        $escapedCwd   = str_replace("'", "''", $absoluteCwd);
        $escapedCmd   = str_replace('"', '`"', $command);

        // We wrap in a single powershell invocation
        $psBlock = "Set-Location -LiteralPath '{$escapedCwd}'; {$command}";

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = array_merge($_ENV, [
            'TERM' => 'xterm-256color',
        ]);

        $process = proc_open(
            ['powershell', '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-Command', $psBlock],
            $descriptorSpec,
            $pipes,
            $absoluteCwd,
            $env
        );

        $stdout   = '';
        $stderr   = '';
        $exitCode = -1;

        if (is_resource($process)) {
            fclose($pipes[0]);
            $stdout   = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr   = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
        }

        $execMs = round((microtime(true) - $startTime) * 1000, 2);

        return $this->result(
            true,
            $stdout ?: '',
            $stderr  ?: '',
            $exitCode,
            $execMs,
            $cwd
        );
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function resolveAbsoluteCwd(string $relativeCwd): string
    {
        if (empty($relativeCwd) || $relativeCwd === '/' || $relativeCwd === '\\') {
            return $this->basePath;
        }

        $candidate = $this->basePath . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relativeCwd), DIRECTORY_SEPARATOR);
        $real      = realpath($candidate);

        // Sandbox: must stay within basePath
        if ($real && str_starts_with($real, $this->basePath)) {
            return $real;
        }

        return $this->basePath;
    }

    private function resolveNewCwd(string $absoluteCwd, string $target): string
    {
        if (empty($target) || $target === '~') {
            return $this->basePath;
        }

        // Absolute paths starting with drive letter or UNC — disallow, stay at base
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $target) || str_starts_with($target, '\\\\')) {
            return $this->basePath;
        }

        $candidate = $absoluteCwd . DIRECTORY_SEPARATOR . $target;
        $real      = realpath($candidate);

        if ($real && is_dir($real) && str_starts_with($real, $this->basePath)) {
            return $real;
        }

        return $absoluteCwd;
    }

    private function toRelativeCwd(string $absolutePath): string
    {
        if ($absolutePath === $this->basePath) {
            return '';
        }

        $rel = substr($absolutePath, strlen($this->basePath));
        return ltrim(str_replace('\\', '/', $rel), '/');
    }

    private function result(bool $ok, string $stdout, string $stderr, int $exitCode, float $execMs, string $cwd): array
    {
        return [
            'ok'              => $ok,
            'stdout'          => $stdout,
            'stderr'          => $stderr,
            'exitCode'        => $exitCode,
            'executionTimeMs' => $execMs,
            'cwd'             => $cwd,
        ];
    }
}
