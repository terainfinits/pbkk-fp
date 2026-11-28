# Coding agent lokal

Jalankan PHP, Git, worker Laravel dan Docker Linux pada komputer yang sama, dengan akses ke API Senopati ITS. Akses IDE melalui loopback. Repo yang didaftarkan harus milik pengguna komputer dan dipercaya. Image tes dipilih oleh pemilik melalui Artisan. Model hanya dapat meminta operasi tes yang sudah ditetapkan dalam image.

## Menjalankan aplikasi

Gunakan database lokal dan queue database. Isi `.env` dengan `QUEUE_CONNECTION=database`, `DB_QUEUE_RETRY_AFTER=660`, `SENOPATI_API_KEY`, `SENOPATI_MODEL` sesuai model yang diberikan ITS, dan `SENOPATI_ENDPOINT` berisi URL lengkap chat completions yang diberikan ITS. Setelah pemasangan awal, jalankan migrasi sekali dan buat akun melalui registrasi. Untuk sesi pengembangan jalankan:

```sh
composer dev
```

Mode lokal (`HARNESS_WORKER_MODE=local`) meluncurkan worker database tersembunyi setelah **Mulai tugas** atau **Lanjutkan**. Worker menghabiskan antrean harness lalu keluar. Jika peluncuran gagal, klik **Coba lagi** untuk membangunkan worker bagi job yang sama.

Untuk server bersama atur `HARNESS_WORKER_MODE=managed` dan jalankan `php artisan queue:work database --queue=harness --tries=1 --timeout=620` melalui service manager. Jalankan scheduler Laravel untuk pemulihan worker yang terputus. Build frontend hanya menyiapkan aset browser.

## Verifikasi tugas

Worker memulai fase planning dengan membaca repository. Rencana memuat tujuan, file, langkah, dan kriteria penerimaan. Jika agent meminta klarifikasi sebelum coding, jawab di panel untuk melanjutkan dari commit awal. Sebelum edit pertama, worker mengambil hasil tes baseline. Setiap patch menghapus hasil verifikasi dari revisi sebelumnya.

Image utama menjalankan pemeriksaan bernama `test`. Untuk pemeriksaan tambahan, daftarkan workspace dengan opsi berulang seperti `--check=lint=php-lint:local --check=build=frontend-build:local`. Setiap image menjalankan entrypoint sendiri terhadap snapshot yang sama. Semua image harus tersedia lokal.

Panel menampilkan bukti per kriteria dan hasil pemeriksaan per revisi. Apply memerlukan status `verified`. Jika run berhenti karena batas langkah, tes gagal, atau kriteria manual, diff tetap tersedia untuk diperiksa. Run lama dengan status `review` dapat ditolak dan dijalankan kembali untuk memperoleh bukti verifikasi.

## Demo dua file

Build image lokal dengan `docker build -t harness-php:local docker/harness`. Image contoh sudah memiliki PHP dan menjalankan `php tests.php` dari salinan snapshot di /tmp. Proyek dengan PHPUnit/Pest perlu image sendiri yang menyertakan dependency terkunci dan entrypoint tes. Runner memakai image lokal dengan jaringan dinonaktifkan.

Di repo Git sementara, buat dan commit dua file ini.

**sum.php**
```php
<?php
function sum(int $a, int $b): int { return $a - $b; }
```

**tests.php**
```php
<?php
require __DIR__.'/sum.php';
exit(sum(2, 3) === 5 ? 0 : 1);
```

Daftarkan root repo dengan `php artisan harness:register "/path/to/repo" anggota@example.com harness-php:local`. Buka **Tugas coding** di IDE dan kirim “Cari fungsi sum, perbaiki hasilnya, tambahkan pengujian bilangan negatif di tests.php, lalu jalankan tes.” Periksa aktivitas, exit code dan diff. Sebelum memilih Terapkan, repo asli harus bersih. Setelah diterapkan, periksa `git status --short` dan `git diff`. File baru muncul sebagai untracked.

Worker mengirim permintaan berautentikasi Bearer ke Senopati dengan format chat completions. API key disimpan pada environment server. Jika Senopati terputus atau image tes tidak tersedia, panel menampilkan kegagalan. Batas run adalah 20 tindakan dan 10 menit. Pembatalan diperiksa di antara tindakan dan selama tes. Permintaan model yang sudah dikirim memiliki timeout sampai 60 detik. Scheduler menandai run yang melewati batas setelah worker mati. Riwayat dapat dibuka kembali melalui workspace yang sama.


Setelah mengubah konfigurasi, jalankan `php artisan config:clear` dan `php artisan queue:restart`. Docker diperlukan untuk tes kode proyek. Akses Senopati dilakukan oleh worker Laravel melalui jaringan host. Endpoint dan akses model harus sesuai informasi ITS. Ollama lokal tidak diperlukan oleh harness.

