# Coding agent lokal

Jalankan PHP, Git, worker Laravel dan Docker Linux pada komputer yang sama, dengan akses ke API Senopati ITS. Akses IDE melalui loopback. Repo yang didaftarkan harus milik pengguna komputer dan dipercaya. Image tes dipilih oleh pemilik melalui Artisan. Model hanya dapat meminta operasi tes yang sudah ditetapkan dalam image.

## Menjalankan aplikasi

Gunakan database lokal dan queue database. Isi `.env` dengan `QUEUE_CONNECTION=database`, `DB_QUEUE_RETRY_AFTER=660`, `SENOPATI_API_KEY`, `SENOPATI_MODEL` sesuai model yang diberikan ITS, dan `SENOPATI_ENDPOINT` berisi URL lengkap chat completions yang diberikan ITS. Jalankan migrasi dan buat akun melalui registrasi. Jalankan proses berikut dari terminal terpisah.

```sh
php artisan migrate
php artisan serve --host=127.0.0.1
npm run dev
php artisan queue:work database --queue=harness --tries=1 --timeout=620
php artisan schedule:work
```

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

