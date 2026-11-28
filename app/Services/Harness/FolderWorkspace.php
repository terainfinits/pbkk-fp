<?php

namespace App\Services\Harness;

use FilesystemIterator;
use Illuminate\Support\Facades\File;
use RuntimeException;

class FolderWorkspace
{
    public static function matches(string $path): bool
    {
        $root = realpath(base_path('folder-generated'));
        return $root !== false && realpath($path) === $root;
    }

    private function permitted(string $name): bool
    {
        if (str_starts_with($name, '.') && !in_array($name, ['.gitignore', '.editorconfig', '.prettierrc', '.eslintignore'], true)) {
            return false;
        }
        return !preg_match('/^(?:vendor$|node_modules$|credentials?(?:\.|$)|secrets?(?:\.|$)|auth\.json$|id_(?:rsa|ed25519|ecdsa)|.*\.(?:key|pem|pfx|p12|jks)$)/i', $name)
            && !preg_match('/[:\x00-\x1f\\\\]|[. ]$/', $name);
    }

    private function copyFiles(string $source, string $dest, array &$hashes, string $prefix = ''): void
    {
        foreach (new FilesystemIterator($source) as $entry) {
            $name = $entry->getFilename();
            $path = $entry->getPathname();
            if (!$this->permitted($name) || $entry->isLink() || realpath($path) !== $path) continue;
            $target = $dest.DIRECTORY_SEPARATOR.$name;
            if ($entry->isDir()) {
                File::ensureDirectoryExists($target);
                $this->copyFiles($path, $target, $hashes, $prefix.$name.'/');
            } elseif ($entry->isFile()) {
                if (!copy($path, $target)) throw new RuntimeException("Gagal menyalin $name.");
                $hashes[$prefix.$name] = hash_file('sha256', $target);
            }
        }
    }

    public function snapshot(Repository $repo, string $source, string $dir): void
    {
        if (is_link($source) || !is_dir($source)) throw new RuntimeException('Folder sumber tidak tersedia.');
        File::ensureDirectoryExists($dir);
        $hashes = [];
        $this->copyFiles(realpath($source), realpath($dir), $hashes);
        $repo->git($dir, ['init']);
        $repo->git($dir, ['config','core.autocrlf','false']);
        $repo->git($dir, ['add','-f','--','.']);
        $repo->git($dir, ['-c','user.name=Agent','-c','user.email=agent@localhost','commit','--allow-empty','-m','Folder snapshot']);
        File::put($dir.'/.git/folder-baseline.json', json_encode($hashes, JSON_THROW_ON_ERROR));
    }

    public function apply(Repository $repo, object $run): void
    {
        $root = realpath($run->path);
        if ($root === false || !is_dir($root)) throw new RuntimeException('Folder sumber tidak tersedia.');
        $lock = fopen(storage_path('framework/folder-'.hash('sha256',$root).'.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX)) throw new RuntimeException('Folder sedang digunakan.');
        $temp = storage_path('app/private/harness/apply-'.bin2hex(random_bytes(10)));
        try {
            foreach (json_decode($run->changes, true, flags: JSON_THROW_ON_ERROR) as $change) {
                $target = $root;
                foreach (explode('/', $change['path']) as $part) {
                    $target .= DIRECTORY_SEPARATOR.$part;
                    if (!$this->permitted($part) || $part === '' || is_link($target)
                        || (file_exists($target) && realpath($target) !== $target)) {
                        throw new RuntimeException('Konflik path: '.$change['path']);
                    }
                }
                if (!array_key_exists('baseline_hash',$change) || is_dir($target)
                    || (file_exists($target) ? hash_file('sha256',$target) : null) !== $change['baseline_hash']) {
                    throw new RuntimeException('File berubah: '.$change['path']);
                }
            }
            if (!$run->patch) return;
            File::ensureDirectoryExists($temp);
            $repo->git($temp, ['init']);
            $args = ['--git-dir='.$temp.'/.git','--work-tree='.$root,'apply','--whitespace=nowarn'];
            $repo->git($root, [...$args,'--check','-'], $run->patch);
            $repo->git($root, [...$args,'-'], $run->patch);
        } finally {
            File::deleteDirectory($temp);
            fclose($lock);
        }
    }
}
