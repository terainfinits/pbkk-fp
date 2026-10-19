<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class WorkspaceService
{
    public function __construct(private LegacyWorkspaceImporter $legacy) {}

    public function root(): string
    {
        $id = Auth::id();
        abort_unless($id !== null, 401);
        $parent = config('ide.workspace_root', base_path('folder-generated/users'));
        abort_if(is_link($parent) || is_link($parent.'/'.$id), 403, 'Invalid workspace root.');
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
        abort_if(in_array('..', $parts, true), 422, 'Paths cannot leave your workspace.');
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
            abort_if(is_link($current), 403, 'Symbolic links are not supported in workspaces.');
            if (file_exists($current)) {
                $real = realpath($current);
                abort_unless($real && str_starts_with($real, $root.DIRECTORY_SEPARATOR), 403);
            }
        }

        return $current;
    }
}
