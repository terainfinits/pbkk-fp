# Memulai tugas dan menjalankan worker otomatis

Klik Mulai tugas menyimpan run dan mengaktifkan pemrosesan queue harness.

## Input

Prompt task, akun, workspace atau Folder IDE. Konfigurasi memilih worker lokal atau worker tetap.

## Output

HTTP 202 berisi ID run dan workspace, event queued dan job tersimpan. UI dapat mengikuti timeline.

## Alur kerja

1. Controller memvalidasi kepemilikan dan kesiapan, menyimpan run dan job dalam transaksi.
2. WorkerLauncher membangunkan worker lokal setelah commit.
3. DrainHarness mengambil lock, menjalankan queue:work dengan stop-when-empty, tries=1 dan timeout=620, lalu mengecek ulang antrean.
4. Retry worker membangunkan job yang sudah ada.
5. Scheduler memulihkan run running atau cancelling yang macet jika scheduler aktif.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Http/Controllers/HarnessController.php](../../app/Http/Controllers/HarnessController.php) | Start, antre, retry dan pembatalan. |
| [app/Services/Harness/WorkerLauncher.php](../../app/Services/Harness/WorkerLauncher.php) | Peluncuran proses lokal dan deteksi worker. |
| [app/Console/Commands/DrainHarness.php](../../app/Console/Commands/DrainHarness.php) | Penguras antrean dengan lock. |
| [config/queue.php](../../config/queue.php) | Mode worker, koneksi dan retry. |
| [routes/console.php](../../routes/console.php) | Pemulihan run macet setiap menit. |
| [database/migrations/0001_01_01_000002_create_jobs_table.php](../../database/migrations/0001_01_01_000002_create_jobs_table.php) | Skema queue database. |
| [database/migrations/2026_11_14_000000_create_harness_tables.php](../../database/migrations/2026_11_14_000000_create_harness_tables.php) | Skema workspace, run dan event. |
| [docs/HARNESS.md](../HARNESS.md) | Panduan operasi lokal dan deployment. |
| [tests/Feature/HarnessTest.php](../../tests/Feature/HarnessTest.php) | Skenario job dan keputusan pengguna. |

## Metode publik pada implementasi terkait

**HarnessController.php**

- `index(Request $request)`
- `startFolder(Request $request, Repository $repo)`
- `start(Request $request, int $workspace, Repository $repo)`
- `history(Request $request, int $workspace)`
- `body(Request $request, int $run, int $event)`
- `show(Request $request, int $run)`
- `action(Request $request, int $run, string $action, Repository $repo)`

**WorkerLauncher.php**

- `wake(int $run) → void`
- `lockPath() → string`
- `missing(object $run) → bool`

**DrainHarness.php**

- `handle() → int`

## Skenario yang tercantum dalam tes

- reviews a two-file change and only applies it to an unchanged owned repository
- keeps a valid action when optional progress is unusable
- distinguishes DNS, connection and timeout failures

## Navigasi dokumentasi

- [API chat bertoken dan log penggunaan](10-api-chat.md)
- [Loop perencanaan dan coding Agent](12-loop.md)
