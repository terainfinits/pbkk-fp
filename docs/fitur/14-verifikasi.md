# Tes Docker, review dan penerapan patch

Hasil tes pada revisi tertentu menentukan apakah perubahan dapat diterapkan.

## Input

Snapshot staged, image utama workspace, pemeriksaan tambahan, patch, revisi, kriteria rencana dan keputusan pengguna.

## Output

Exit code dan output per check, status verified atau review, diff, lalu applied, rejected atau conflict.

## Alur kerja

1. Worker mengekspor index Git dan menjalankan entrypoint image terhadap salinan itu.
2. Runner membatasi jaringan, resource dan hak container.
3. Image PHP contoh memanggil tests.php.
4. Perubahan baru membatalkan bukti tes sebelumnya.
5. Finish memerlukan bukti untuk kriteria, tes hijau dan review manusia jika ada kriteria manual.
6. Apply memeriksa revisi patch dan baseline.
7. Konflik satu file menghentikan Apply folder sebelum penulisan.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Services/Harness/Tools.php](../../app/Services/Harness/Tools.php) | Eksekusi tes di Docker dan pengambilan stdout/stderr. |
| [docker/harness/Dockerfile](../../docker/harness/Dockerfile) | Definisi image untuk tests.php. |
| [app/Jobs/RunCodingAgent.php](../../app/Jobs/RunCodingAgent.php) | Mengikat check dengan revisi dan memproses finish. |
| [app/Http/Controllers/HarnessController.php](../../app/Http/Controllers/HarnessController.php) | Keputusan apply dan reject. |
| [app/Services/Harness/Repository.php](../../app/Services/Harness/Repository.php) | Patch Git dan pemeriksaan repo. |
| [app/Services/Harness/FolderWorkspace.php](../../app/Services/Harness/FolderWorkspace.php) | Membandingkan hash file target sebelum apply. |
| [resources/js/components/agent-ide/DiffViewer.vue](../../resources/js/components/agent-ide/DiffViewer.vue) | Review perubahan per file. |
| [database/migrations/2026_11_25_000000_add_harness_verification.php](../../database/migrations/2026_11_25_000000_add_harness_verification.php) | Kolom rencana, pemeriksaan dan bukti. |

## Metode publik pada implementasi terkait

**Tools.php**

- `files(string $dir) → array`
- `read(string $dir, string $path, int $start = 1, int $count = 100) → string`
- `edit(string $dir, string $path, string $old, string $new) → string`
- `search(string $dir, string $text) → string`
- `test(string $dir, object $space, int $id, callable $check) → array`

**RunCodingAgent.php**

- `handle(Repository $repo, Tools $tools) → void`
- `failed(?Throwable $e) → void`

**HarnessController.php**

- `index(Request $request)`
- `startFolder(Request $request, Repository $repo)`
- `start(Request $request, int $workspace, Repository $repo)`
- `history(Request $request, int $workspace)`
- `body(Request $request, int $run, int $event)`
- `show(Request $request, int $run)`
- `action(Request $request, int $run, string $action, Repository $repo)`

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

## Navigasi dokumentasi

- [Isolasi perubahan melalui worktree dan snapshot folder](13-snapshot.md)
- [Riwayat run, timeline dan raw HTTP Senopati](15-transkrip.md)
