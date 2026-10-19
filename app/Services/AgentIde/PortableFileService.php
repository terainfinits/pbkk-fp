<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\File;

class PortableFileService
{
    public function __construct(private WorkspaceService $workspace) {}

    /**
     * @param  array<string, string>  $extra
     * @return array<string, mixed>
     */
    public function operate(string $action, string $relative, array $extra = []): array
    {
        $relative = $this->workspace->normalize($relative, $action === 'tree');
        $path = $this->workspace->resolve($relative, $action === 'tree');
        if ($action === 'tree') {
            abort_unless(is_dir($path), 404, 'Directory not found.');

            return ['root' => 'My workspace', 'currentPath' => '/'.$relative, 'tree' => $this->tree($relative)];
        }
        if ($action === 'delete') {
            abort_unless(file_exists($path), 404, 'File or directory not found.');
            $this->remove($relative);

            return ['path' => $relative];
        }
        if ($action === 'create') {
            abort_if(file_exists($path), 409, 'This file or directory already exists.');
        }
        if ($action === 'create' && ($extra['type'] ?? '') === 'directory') {
            File::ensureDirectoryExists($path, 0700);
            $this->workspace->resolve($relative);

            return ['ok' => true, 'path' => $relative];
        }
        if ($action === 'read') {
            abort_unless(is_file($path), 404, 'File not found.');
        } else {
            abort_if(file_exists($path) && ! is_file($path), 422, 'Only regular files can be edited.');
            File::ensureDirectoryExists(dirname($path), 0700);
            $path = $this->workspace->resolve($relative);
        }
        $handle = @fopen($path, $action === 'read' ? 'rb' : ($action === 'create' ? 'x+b' : 'c+b'));
        abort_unless($handle !== false, $action === 'create' && file_exists($path) ? 409 : 422, 'The file could not be opened.');
        try {
            abort_unless(flock($handle, $action === 'read' ? LOCK_SH : LOCK_EX), 422, 'The file could not be locked.');
            $this->workspace->resolve($relative);
            $info = fstat($handle);
            abort_unless($info !== false && ($info['mode'] & 0170000) === 0100000, 422, 'Only regular files are supported.');
            if ($action === 'read') {
                abort_if($info['size'] > 1_000_000, 422, 'File is too large for the editor (1 MB maximum).');
                $content = stream_get_contents($handle, 1_000_001);
                abort_unless($content !== false, 422, 'The file could not be read.');

                return ['path' => $relative, 'filename' => basename($path), 'extension' => pathinfo($path, PATHINFO_EXTENSION),
                    'content' => $content, 'size' => strlen($content), 'lastModified' => $info['mtime']];
            }
            $content = $extra['content'] ?? '';
            abort_unless(ftruncate($handle, 0), 422, 'The file could not be updated.');
            $written = 0;
            while ($written < strlen($content)) {
                $count = fwrite($handle, substr($content, $written));
                abort_unless($count !== false && $count > 0, 422, 'The file could not be saved.');
                $written += $count;
            }
            fflush($handle);

            return ['ok' => true, 'path' => $relative, 'lastModified' => time()];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @return list<array<string, mixed>> */
    private function tree(string $relative, int $depth = 0): array
    {
        if ($depth > 5) {
            return [];
        }
        $result = [];
        foreach (new \DirectoryIterator($this->workspace->resolve($relative, true)) as $entry) {
            if ($entry->isDot()) {
                continue;
            }
            if (in_array($entry->getFilename(), ['.git', 'vendor', 'node_modules', '.legacy-imported'], true) || $this->workspace->isLink($entry->getPathname())) {
                continue;
            }
            if (! $entry->isDir() && ! $entry->isFile()) {
                continue;
            }
            $path = ($relative === '' ? '' : $relative.'/').$entry->getFilename();
            $item = ['name' => $entry->getFilename(), 'path' => $path, 'isDir' => $entry->isDir(), 'extension' => $entry->getExtension()];
            if ($entry->isDir()) {
                $item['children'] = $this->tree($path, $depth + 1);
            } else {
                $item['size'] = $entry->getSize();
            }
            $result[] = $item;
            if (count($result) >= 2000) {
                break;
            }
        }
        usort($result, fn ($a, $b) => ($b['isDir'] <=> $a['isDir']) ?: strcasecmp($a['name'], $b['name']));

        return $result;
    }

    private function remove(string $relative): void
    {
        $path = $this->workspace->resolve($relative);
        if (is_dir($path)) {
            foreach (new \DirectoryIterator($path) as $entry) {
                if ($entry->isDot()) {
                    continue;
                }
                $this->remove($relative.'/'.$entry->getFilename());
            }
            abort_unless(@rmdir($path), 422, 'Directory could not be deleted.');
        } else {
            abort_unless(@unlink($path), 422, 'File could not be deleted.');
        }
    }
}
