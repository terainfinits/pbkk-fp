# Riwayat run, timeline dan raw HTTP Senopati

Pengguna dapat mengikuti eksekusi dan membandingkan instruksi, balasan model serta hasil tool per giliran.

## Input

Workspace dan run milik pengguna, cursor after berupa ID event terakhir, ID event body untuk membuka request atau response.

## Output

Timeline bertambah tiap polling 1,5 detik. Tautan raw menyajikan body sebagai teks. Kegagalan transport tercatat walaupun tidak ada body respons.

## Alur kerja

1. Worker mencatat event sebelum dan sesudah panggilan.
2. SenopatiAgent menyerahkan body ke callback perekam.
3. Arsip body lengkap per panggilan ada di storage/app/private/harness-transcripts/{run}, dengan API key terkonfigurasi dan pola Bearer disamarkan.
4. Event database menjadi indeks arsip.
5. Endpoint body memeriksa kepemilikan run.
6. Untuk menganalisis edit gagal, bandingkan response giliran N, tool_error dan request N+1.
7. Run lama hanya menampilkan data yang sempat direkam.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [resources/js/components/agent-ide/HarnessPanel.vue](../../resources/js/components/agent-ide/HarnessPanel.vue) | Pemilih run, polling, timeline dan tautan raw. |
| [routes/harness.php](../../routes/harness.php) | Rute daftar, detail, body dan aksi. |
| [app/Http/Controllers/HarnessController.php](../../app/Http/Controllers/HarnessController.php) | Query milik pengguna dan pengiriman body privat. |
| [app/Jobs/RunCodingAgent.php](../../app/Jobs/RunCodingAgent.php) | Event executor dan penyimpanan body. |
| [app/Services/Harness/SenopatiAgent.php](../../app/Services/Harness/SenopatiAgent.php) | Body request dan response aktual. |
| [app/Services/SenopatiConnection.php](../../app/Services/SenopatiConnection.php) | Kategori DNS, koneksi dan timeout. |
| [database/migrations/2026_11_25_010000_add_event_activity.php](../../database/migrations/2026_11_25_010000_add_event_activity.php) | Kolom giliran, waktu dan ID aktivitas. |
| [tests/Feature/HarnessTest.php](../../tests/Feature/HarnessTest.php) | Pengujian urutan event dan kegagalan provider. |

## Metode publik pada implementasi terkait

**HarnessController.php**

- `index(Request $request)`
- `startFolder(Request $request, Repository $repo)`
- `start(Request $request, int $workspace, Repository $repo)`
- `history(Request $request, int $workspace)`
- `body(Request $request, int $run, int $event)`
- `show(Request $request, int $run)`
- `action(Request $request, int $run, string $action, Repository $repo)`

**RunCodingAgent.php**

- `handle(Repository $repo, Tools $tools) → void`
- `failed(?Throwable $e) → void`

**SenopatiAgent.php**

- `recordBodies(\Closure $record) → void`
- `next(array $messages, int $timeout) → string`

**SenopatiConnection.php**

- `describe(Throwable $exception) → string`

## Skenario yang tercantum dalam tes

- reviews a two-file change and only applies it to an unchanged owned repository
- keeps a valid action when optional progress is unusable
- distinguishes DNS, connection and timeout failures

## Navigasi dokumentasi

- [Tes Docker, review dan penerapan patch](14-verifikasi.md)
