<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class WorkspaceService
{
    public function __construct(private LegacyWorkspaceImporter $legacy, private RuntimeService $runtimes) {}

    public function root(): string
    {
        $id = Auth::id();
        abort_unless($id !== null, 401);
        $parent = config('ide.workspace_root', base_path('folder-generated/users'));
        abort_if($this->isLink($parent) || $this->isLink($parent.'/'.$id), 403, 'Invalid workspace root.');
        File::ensureDirectoryExists($parent.'/'.$id, 0700);
        $this->legacy->import($parent.'/'.$id);
        $root = realpath($parent.'/'.$id);
        abort_unless($root !== false, 500, 'Workspace could not be created.');

        return $root;
    }

    public function normalize(string $path, bool $allowRoot = false): string
    {
        $path = str_replace('\\', '/', $path);
        abort_if(str_contains($path, "\0") || str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:/', $path) === 1, 422, 'Use a relative workspace path.');
        $parts = explode('/', $path);
        if (in_array('..', $parts, true)) {
            abort(422, 'Paths cannot leave your workspace.');
        }
        if ($this->runtimes->isWindows()) {
            foreach ($parts as $part) {
                if ($part === '' || $part === '.') {
                    continue;
                }
                abort_if(preg_match('/[<>:"|?*\x00-\x1F]/', $part) === 1 || preg_match('/[. ]$/', $part) === 1
                    || preg_match('/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\.|$)/i', $part) === 1, 422, 'This filename is not supported on Windows.');
            }
        }
        $path = implode('/', array_values(array_filter($parts, fn ($part) => $part !== '' && $part !== '.')));
        abort_if(! $allowRoot && $path === '', 422, 'A file or directory path is required.');

        return $path;
    }

    public function resolve(string $path, bool $allowRoot = false): string
    {
        $relative = $this->normalize($path, $allowRoot);
        $root = $this->root();
        $current = $root;
        foreach ($relative === '' ? [] : explode('/', $relative) as $part) {
            $current .= '/'.$part;
            clearstatcache(true, $current);
            abort_if($this->isLink($current), 403, 'Symbolic links and junctions are not supported in workspaces.');
            if (file_exists($current)) {
                $real = realpath($current);
                abort_unless($real && str_starts_with($real, $root.DIRECTORY_SEPARATOR), 403);
            }
        }

        return $current;
    }

    public function isLink(string $path): bool
    {
        if (is_link($path)) {
            return true;
        }
        if (! $this->runtimes->isWindows()) {
            return false;
        }
        $normalize = fn (string $value) => preg_replace('#^//\?/#', '', str_replace('\\', '/', $value));
        // readlink also identifies dangling NTFS junctions that file_exists misses.
        $target = @readlink($path);
        if ($target !== false && strcasecmp($normalize($target), $normalize($path)) !== 0) {
            return true;
        }
        $real = realpath($path);

        // is_link() alone does not reliably identify NTFS directory junctions.
        return $real !== false && strcasecmp($normalize($real), $normalize($path)) !== 0;
    }
}
