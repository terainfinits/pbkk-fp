<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class LegacyWorkspaceImporter
{
    public function import(string $destination): void
    {
        $lock = fopen(dirname($destination).'/.import-'.basename($destination), 'c');
        if ($lock === false) {
            abort(500, 'Could not lock the workspace import.');
        }
        flock($lock, LOCK_EX);
        try {
            $this->copyForOwner($destination);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function copyForOwner(string $destination): void
    {
        $email = config('ide.legacy_owner_email');
        if (! $email || strcasecmp(Auth::user()->email, $email) !== 0 || is_file($destination.'/.legacy-imported')) {
            return;
        }
        $source = config('ide.legacy_root', base_path('folder-generated'));
        $entries = [];
        foreach (new \DirectoryIterator($source) as $entry) {
            if ($entry->isDot() || $entry->getFilename() === 'users' || str_starts_with($entry->getFilename(), '.')) {
                continue;
            }
            $entries[] = $entry->getPathname();
        }
        // Refuse conflicts or links before copying anything. Original files remain intact.
        foreach ($entries as $entry) {
            abort_if(file_exists($destination.'/'.basename($entry)), 409, 'Legacy import has a filename conflict. Ask the administrator to import manually.');
            abort_if(is_link($entry), 409, 'Legacy import contains a symbolic link.');
            if (is_dir($entry)) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($entry, \FilesystemIterator::SKIP_DOTS)) as $child) {
                    abort_if($child->isLink(), 409, 'Legacy import contains a symbolic link.');
                }
            }
        }
        foreach ($entries as $entry) {
            $target = $destination.'/'.basename($entry);
            if (is_dir($entry)) {
                File::copyDirectory($entry, $target);
            } else {
                File::copy($entry, $target);
            }
        }
        File::put($destination.'/.legacy-imported', now()->toIso8601String());
    }
}
