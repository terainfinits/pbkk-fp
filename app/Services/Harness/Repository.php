<?php

namespace App\Services\Harness;

use RuntimeException;
use Symfony\Component\Process\Process;

class Repository
{
    public function git(string $dir, array $args, ?string $input = null): string
    {
        $process = new Process(['git', '-c', 'core.hooksPath='.storage_path('framework/disabled-hooks'), '-c', 'core.fsmonitor=false', ...$args], $dir);
        $process->setInput($input)->setTimeout(30)->mustRun();

        return $process->getOutput();
    }

    public function cleanHead(string $dir): string
    {
        if ($this->git($dir, ['status', '--porcelain', '--untracked-files=all']) !== '') {
            throw new RuntimeException('Workspace memiliki perubahan lokal.');
        }

        return trim($this->git($dir, ['rev-parse', '--verify', 'HEAD']));
    }

    public function tree(int $id): string
    {
        return storage_path('app/private/harness/run-'.$id);
    }

    public function checkout(object $run, object $workspace): string
    {
        $dir = $this->tree($run->id);
        if (!is_dir(dirname($dir))) {
            \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($dir));
        }
        $this->git($workspace->path, ['worktree', 'add', '--detach', $dir, $run->base]);

        return $dir;
    }

    public function discard(object $workspace, int $id): void
    {
        if (is_dir($this->tree($id))) {
            $this->git($workspace->path, ['worktree', 'remove', '--force', $this->tree($id)]);
        }
    }

    public function patch(string $dir): string
    {
        $patch = $this->git($dir, ['diff', '--cached', '--no-ext-diff', '--no-textconv', '--binary', 'HEAD']);
        if (strlen($patch) > 500000) {
            throw new RuntimeException('Patch melebihi 500 KB.');
        }

        return $patch;
    }

    public function applyToolPatch(string $dir, string $patch): void
    {
        // Only plain text unified diffs. Git checks paths, pre-images and symlink ancestors.
        if (strlen($patch) > 100000 || !str_contains($patch, '@@ ') ||
            preg_match('/^(?:.*(?:old mode|new mode|file mode) (?:120000|160000)|GIT binary patch|rename from|copy from)/m', $patch)) {
            throw new RuntimeException('Gunakan unified diff file teks biasa.');
        }
        $this->git($dir, ['apply', '--index', '--whitespace=nowarn', '-'], rtrim($patch, "\r\n")."\n");
    }

    public function changes(string $dir): array
    {
        $paths = explode("\0", $this->git($dir, ['diff', '--cached', '--no-renames', '--name-only', '-z', 'HEAD']));
        $changes = [];
        $bytes = 0;
        foreach (array_filter($paths, 'strlen') as $path) {
            $versions = [];
            foreach (['HEAD:', ':'] as $prefix) {
                try {
                    $versions[] = $this->git($dir, ['show', $prefix.$path]);
                } catch (\Symfony\Component\Process\Exception\ProcessFailedException) {
                    $versions[] = '';
                }
            }
            [$before, $after] = $versions;
            $bytes += strlen($before) + strlen($after);
            $changes[] = ['path' => $path, 'before' => $bytes < 1000000 ? $before : null,
                'after' => $bytes < 1000000 ? $after : null];
        }
        return $changes;
    }
}
