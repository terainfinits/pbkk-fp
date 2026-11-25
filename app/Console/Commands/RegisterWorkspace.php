<?php
// Fitur: Harness tugas coding
// connect with ../../../database/migrations/0001_01_01_000000_create_users_table.php, ../../../database/migrations/2026_11_14_000000_create_harness_tables.php, ../../../docs/HARNESS.md
// connect with ../../../resources/js/components/agent-ide/HarnessPanel.vue, ../../../tests/Feature/HarnessTest.php, ../../../tests/Feature/LiveHarnessTest.php
// connect with ../../Services/Harness/Repository.php

namespace App\Console\Commands;

use App\Services\Harness\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegisterWorkspace extends Command
{
    protected $signature = 'harness:register {path} {email} {image} {--check=* : Additional required check as name=image}';
    protected $description = 'Register a clean local repository with its offline test image';

    public function handle(Repository $repo): int
    {
        $path = realpath($this->argument('path'));
        $owner = DB::table('users')->where('email', $this->argument('email'))->value('id');
        $image = $this->argument('image');
        if (!$owner || !$path || !preg_match('/^[a-z0-9][a-z0-9._\/:@-]+$/D', $image)) {
            $this->error('Periksa email pengguna, lokasi repo, dan nama image.');
            return 1;
        }
        $root = realpath(trim($repo->git($path, ['rev-parse', '--show-toplevel'])));
        if ($root !== $path || str_contains($path, ',')) {
            $this->error('Pilih root repository Git.');
            return 1;
        }
        $checks = [];
        foreach ($this->option('check') as $entry) {
            if (!preg_match('~^([a-z][a-z0-9_-]*)=([a-z0-9][a-z0-9._/:@-]+)$~D', $entry, $m)
                || in_array($m[1], ['manual', 'test'], true) || isset($checks[$m[1]])) {
                $this->error('Gunakan --check=nama=image dengan nama unik.');
                return 1;
            }
            $checks[$m[1]] = $m[2];
        }
        $repo->cleanHead($path);
        DB::table('workspaces')->updateOrInsert(['path' => $path, 'user_id' => $owner],
            ['image' => $image, 'extra_checks' => json_encode($checks), 'name' => basename($path)]);
        $this->info('Workspace terdaftar.');
        return 0;
    }
}
