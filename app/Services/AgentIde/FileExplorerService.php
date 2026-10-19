<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\File;

/**
 * Handles every read/write/scan operation against the workspace on disk.
 * Centralising this here means every path-safety check (staying inside
 * base_path()) lives in one place instead of being repeated per-endpoint.
 */
class FileExplorerService {
    /** Directories/files that are never shown or touched by the IDE. */
    private const IGNORED = [
        '.git', 'vendor', 'node_modules', '.gemini', 'storage/framework',
        '.php-cs-fixer.cache', 'package-lock.json', 'composer.lock', '.system_generated',
    ];

    private const MAX_TREE_DEPTH = 5;

    public function basePath(): string {
        $path = base_path('folder-generated');

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    /**
     * Resolve a user-supplied relative path to an absolute path that is
     * guaranteed to live inside base_path(). Returns null if it doesn't.
     */
    public function resolveExistingPath(string $relativePath): ?string {
        $basePath = $this->basePath();
        $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . $relativePath);

        if (!$fullPath || !str_starts_with($fullPath, $basePath)) {
            return null;
        }

        return $fullPath;
    }

    /**
     * Build a safe absolute path for a file/dir that does not need to
     * exist yet (create operations), stripping any directory traversal.
     */
    public function safeNewPath(string $relativePath): array {
        $cleanRelPath = ltrim(str_replace(['../', '..\\'], '', $relativePath), '/\\');
        $fullPath = $this->basePath() . DIRECTORY_SEPARATOR . $cleanRelPath;

        return [$cleanRelPath, $fullPath];
    }

    public function scanTree(string $targetDir = ''): array {
        $basePath = $this->basePath();
        $fullPath = empty($targetDir)
            ? $basePath
            : (realpath($basePath . DIRECTORY_SEPARATOR . $targetDir) ?: $basePath);

        if (!str_starts_with($fullPath, $basePath)) {
            $fullPath = $basePath;
        }

        return [
            'root' => basename($basePath),
            'currentPath' => str_replace($basePath, '', $fullPath) ?: '/',
            'tree' => $this->scanDirectory($fullPath, $basePath),
        ];
    }

    private function scanDirectory(string $dir, string $basePath, int $depth = 0): array {
        if ($depth > self::MAX_TREE_DEPTH || !is_dir($dir)) {
            return [];
        }

        $files = scandir($dir);
        if ($files === false) {
            return [];
        }

        $items = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $relativeNormalized = str_replace('\\', '/', ltrim(str_replace($basePath, '', $filePath), '/\\'));

            if ($this->isIgnored($file, $relativeNormalized)) {
                continue;
            }

            $isDir = is_dir($filePath);
            $item = [
                'name' => $file,
                'path' => $relativeNormalized,
                'isDir' => $isDir,
                'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            ];

            $item = $isDir
                ? $item + ['children' => $this->scanDirectory($filePath, $basePath, $depth + 1)]
                : $item + ['size' => filesize($filePath)];

            $items[] = $item;
        }

        usort($items, function ($a, $b) {
            return $a['isDir'] === $b['isDir']
                ? strcasecmp($a['name'], $b['name'])
                : ($a['isDir'] ? -1 : 1);
        });

        return $items;
    }

    private function isIgnored(string $file, string $relativeNormalized): bool {
        foreach (self::IGNORED as $pattern) {
            if ($file === $pattern || str_contains($relativeNormalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function readFile(string $relativePath): ?array {
        $fullPath = $this->resolveExistingPath($relativePath);
        if (!$fullPath || !is_file($fullPath)) {
            return null;
        }

        $content = file_get_contents($fullPath);

        return [
            'path' => str_replace('\\', '/', $relativePath),
            'filename' => basename($fullPath),
            'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
            'content' => $content,
            'size' => strlen($content),
            'lastModified' => filemtime($fullPath),
        ];
    }

    public function saveFile(string $relativePath, string $content): array {
        [$cleanRelPath, $fullPath] = $this->safeNewPath($relativePath);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, $content);

        return [
            'path' => str_replace('\\', '/', $cleanRelPath),
            'lastModified' => filemtime($fullPath),
        ];
    }

    /**
     * @return array{ok: bool, path?: string, error?: string}
     */
    public function createItem(string $relativePath, string $type, string $initialContent = ''): array {
        [$cleanRelPath, $fullPath] = $this->safeNewPath($relativePath);

        if (file_exists($fullPath)) {
            return ['ok' => false, 'error' => 'Item already exists'];
        }

        if ($type === 'directory') {
            mkdir($fullPath, 0755, true);
        } else {
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($fullPath, $initialContent);
        }

        return ['ok' => true, 'path' => str_replace('\\', '/', $cleanRelPath)];
    }

    public function deleteItem(string $relativePath): ?string {
        $fullPath = $this->resolveExistingPath($relativePath);
        if (!$fullPath) {
            return null;
        }

        if (is_dir($fullPath)) {
            File::deleteDirectory($fullPath);
        } else {
            unlink($fullPath);
        }

        return str_replace('\\', '/', $relativePath);
    }

    /**
     * Rename an existing file or directory. Only the final name changes;
     * the parent directory stays the same. Returns the new relative path
     * on success, or null on failure.
     */
    public function renameItem(string $relativePath, string $newName): ?string {
        $fullPath = $this->resolveExistingPath($relativePath);
        if (!$fullPath) {
            return null;
        }

        // Reject empty or traversal-sounding names.
        $cleanName = ltrim(str_replace(['/', '\\'], '', $newName), '.');
        if ($cleanName === '') {
            return null;
        }

        $parentDir = dirname($fullPath);
        $newFullPath = $parentDir . DIRECTORY_SEPARATOR . $cleanName;

        // Safety: must stay inside basePath.
        $basePath = $this->basePath();
        if (!str_starts_with($newFullPath, $basePath)) {
            return null;
        }

        if (file_exists($newFullPath)) {
            return null;
        }

        if (!@rename($fullPath, $newFullPath)) {
            return null;
        }

        // Return the new relative path (forward-slash normalised).
        $rel = str_replace('\\', '/', ltrim(str_replace($basePath, '', $newFullPath), '/\\'));
        return $rel;
    }

    /**
     * Upload a single file or extract a .zip archive into the workspace.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $targetDirectory  Relative directory inside basePath (may be empty).
     * @return array{ok:bool, path?:string, error?:string}
     */
    public function uploadFile($file, string $targetDirectory = ''): array {
        if (!$file || !$file->isValid()) {
            return ['ok' => false, 'error' => 'Invalid file upload.'];
        }

        $originalName = $file->getClientOriginalName();
        $cleanName = basename($originalName);
        if ($cleanName === '') {
            return ['ok' => false, 'error' => 'Invalid file name.'];
        }

        $isZip = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION)) === 'zip';

        // Resolve target directory safely.
        [$cleanRelDir, $fullDir] = $this->safeNewPath($targetDirectory);
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $basePath = $this->basePath();

        if (!$isZip) {
            $dest = $fullDir . DIRECTORY_SEPARATOR . $cleanName;
            if (file_exists($dest)) {
                return ['ok' => false, 'error' => 'A file with that name already exists.'];
            }
            if (!is_writable($fullDir)) {
                return ['ok' => false, 'error' => 'Directory not writable: ' . $fullDir];
            }
            try {
                $file->move($fullDir, $cleanName);
            } catch (\Throwable $e) {
                return ['ok' => false, 'error' => 'Failed to save file: ' . $e->getMessage()];
            }
            $rel = str_replace('\\', '/', ltrim(str_replace($basePath, '', $dest), '/\\'));
            return ['ok' => true, 'path' => $rel];
        }

        // --- ZIP handling ---
        $tempZip = tempnam(sys_get_temp_dir(), 'upload_') . '.zip';
        $file->move(dirname($tempZip), basename($tempZip));

        $zip = new \ZipArchive();
        if ($zip->open($tempZip) !== true) {
            @unlink($tempZip);
            return ['ok' => false, 'error' => 'Invalid or corrupt ZIP archive.'];
        }

        // Zip-slip protection + size guard.
        $maxBytes = 100 * 1024 * 1024; // 100 MB ceiling
        $totalExtracted = 0;
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                @unlink($tempZip);
                return ['ok' => false, 'error' => 'Corrupt ZIP entry detected.'];
            }
            $name = $stat['name'];
            // Reject absolute paths and traversal.
            if (preg_match('/^([A-Za-z]:[\\\\\/]|[\/\\\\])/', $name) || str_contains($name, '../') || str_contains($name, '..\\')) {
                @unlink($tempZip);
                return ['ok' => false, 'error' => 'ZIP contains unsafe path: ' . $name];
            }
            $totalExtracted += $stat['size'];
            if ($totalExtracted > $maxBytes) {
                @unlink($tempZip);
                return ['ok' => false, 'error' => 'ZIP extraction size exceeds allowed limit.'];
            }
            $entries[] = $name;
        }

        // No silent overwrite: check every entry first.
        foreach ($entries as $entry) {
            $dest = $fullDir . DIRECTORY_SEPARATOR . $entry;
            if (file_exists($dest)) {
                @unlink($tempZip);
                return ['ok' => false, 'error' => 'An item with that name already exists in the target directory.'];
            }
        }

        // Extract.
        $zip->extract($fullDir);
        $zip->close();
        @unlink($tempZip);

        $rel = str_replace('\\', '/', ltrim(str_replace($basePath, '', $fullDir), '/\\'));
        return ['ok' => true, 'path' => $rel];
    }
}
