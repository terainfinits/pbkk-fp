# Isolasi perubahan melalui worktree dan snapshot folder

Setiap tugas mendapat direktori kerja terpisah dari sumber.

## Input

Workspace terdaftar dengan base commit, atau isi Folder IDE yang telah tersimpan di disk, serta ID run.

## Output

Worktree Git atau repo sementara dengan baseline. Operasi edit menghasilkan perubahan staged dan diff.

## Alur kerja

1. Repo biasa diperiksa bersih lalu dibuatkan worktree detached.
2. Untuk Folder IDE, worker menyalin file yang diizinkan, membuat commit baseline di direktori privat, dan mencatat hash file.
3. File baru yang tersimpan ikut snapshot.
4. Filter melewatkan metadata, nama kredensial tertentu dan tautan.
5. Folder kosong dapat diproses.
6. Setelah run berakhir, direktori sementara dibersihkan.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Services/Harness/Repository.php](../../app/Services/Harness/Repository.php) | Checkout, staging, diff dan pelepasan worktree. |
| [app/Services/Harness/FolderWorkspace.php](../../app/Services/Harness/FolderWorkspace.php) | Filter snapshot, manifest hash dan penguncian Apply. |
| [app/Jobs/RunCodingAgent.php](../../app/Jobs/RunCodingAgent.php) | Mengelola siklus hidup direktori kerja. |
| [tests/Feature/FolderWorkspaceTest.php](../../tests/Feature/FolderWorkspaceTest.php) | Menguji pembuatan file dan konflik. |
| [app/Services/AgentIde/Projects.php](../../app/Services/AgentIde/Projects.php) | Menentukan apakah folder siap dipakai. |

## Metode publik pada implementasi terkait

**Repository.php**

- `git(string $dir, array $args, ?string $input = null) → string`
- `cleanHead(string $dir) → string`
- `tree(int $id) → string`
- `checkout(object $run, object $workspace) → string`
- `discard(object $workspace, int $id) → void`
- `patch(string $dir) → string`
- `applyToolPatch(string $dir, string $patch) → void`
- `changes(string $dir) → array`

**FolderWorkspace.php**

- `matches(string $path) → bool`
- `snapshot(Repository $repo, string $source, string $dir) → void`
- `apply(Repository $repo, object $run) → void`

**RunCodingAgent.php**

- `handle(Repository $repo, Tools $tools) → void`
- `failed(?Throwable $e) → void`

**Projects.php**

- `current() → ?object`
- `reason(string $path, ?string $image) → ?string`
- `listing() → array`
- `prepare()`
- `folderWorkspace() → int`

## Skenario yang tercantum dalam tes

- reviews a private snapshot and applies all files only with matching originals

## Navigasi dokumentasi

- [Loop perencanaan dan coding Agent](12-loop.md)
- [Tes Docker, review dan penerapan patch](14-verifikasi.md)
