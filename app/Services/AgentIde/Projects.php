<?php

namespace App\Services\AgentIde;

use App\Services\Harness\Repository;
use Illuminate\Support\Facades\DB;

class Projects
{
    public function __construct(private Repository $repo) {}

    public function current(): ?object
    {
        $id = request()->query('project', 'ide');
        if ($id === 'ide') return null;
        abort_unless(is_string($id) && ctype_digit($id), 404);
        $space = DB::table('workspaces')->where('user_id', request()->user()->id)->find($id);
        abort_unless($space, 404);
        return $space;
    }

    public function reason(string $path, ?string $image): ?string
    {
        if (!is_dir($path)) return 'Folder belum tersedia.';
        if (\App\Services\Harness\FolderWorkspace::matches($path)) {
            if (!is_readable($path) || !is_writable($path)) return 'Folder IDE tidak dapat dibaca atau ditulis.';
            try {
                $this->repo->git($path, ['--version']);
            } catch (\RuntimeException) {
                return 'Git tidak dapat dijalankan oleh Laravel.';
            }
            if (!$image) return 'Runner belum dikonfigurasi. Isi IDE_RUNNER_IMAGE dengan image tes proyek.';
            $probe = new \Symfony\Component\Process\Process(['docker','image','inspect','--format','{{.Id}}',$image]);
            try {
                if ($probe->setTimeout(5)->run() === 0) return null;
            } catch (\RuntimeException) {}
            return 'Runner Docker tidak tersedia. Periksa Docker dan image tes proyek.';
        }
        try {
            $root = trim($this->repo->git($path, ['rev-parse','--show-toplevel']));
        } catch (\RuntimeException) {
            return 'Inisialisasi repository Git di folder ini untuk memakai Agent.';
        }
        if (realpath($root) !== realpath($path)) return 'Folder ini subfolder Git. Pilih repo induk yang sudah didaftarkan secara eksplisit.';
        try {
            $this->repo->git($path, ['rev-parse','--verify','HEAD']);
        } catch (\RuntimeException) {
            return 'Folder ini belum memiliki commit awal.';
        }
        if (trim($this->repo->git($path, ['status','--porcelain'])) !== '') return 'Simpan dan commit perubahan lokal sebelum memulai tugas.';
        return $image ? null : 'Konfigurasikan image runner dengan harness:register atau IDE_RUNNER_IMAGE.';
    }

    public function listing(): array
    {
        $folder = base_path('folder-generated');
        $default = ['id'=>'ide','name'=>'Folder IDE (folder-generated)','workspace'=>null];
        $others = [];
        foreach (DB::table('workspaces')->where('user_id',request()->user()->id)->orderBy('name')->get() as $space) {
            $reason = $this->reason($space->path, $space->image);
            if (realpath($folder) && realpath($folder) === realpath($space->path)) {
                $default['workspace'] = $space->id;
                $default['reason'] = $this->reason($folder, config('services.ide_runner_image'));
            } else {
                $others[] = ['id'=>(string)$space->id,'name'=>$space->name,'workspace'=>$space->id,'reason'=>$reason];
            }
        }
        if (!array_key_exists('reason',$default)) $default['reason'] = $this->reason($folder,config('services.ide_runner_image'));
        return [$default, ...$others];
    }

    public function prepare()
    {
        return response()->json(['workspace'=>$this->folderWorkspace()]);
    }

    public function folderWorkspace(): int
    {
        $path = realpath(base_path('folder-generated'));
        $image = config('services.ide_runner_image');
        $reason = $this->reason($path ?: '', $image);
        abort_if($reason !== null, 422, $reason ?? '');
        return DB::transaction(function () use ($path, $image) {
            $owner = request()->user()->id;
            DB::table('users')->where('id', $owner)->lockForUpdate()->first();
            foreach (DB::table('workspaces')->where('user_id', $owner)->get() as $space) {
                if (realpath($space->path) === $path) {
                    DB::table('workspaces')->where('id', $space->id)->update(['image'=>$image]);
                    return $space->id;
                }
            }
            return DB::table('workspaces')->insertGetId(['user_id'=>$owner, 'path'=>$path, 'name'=>basename($path), 'image'=>$image]);
        });
    }
}
