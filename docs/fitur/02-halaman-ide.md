# Startup dan navigasi IDE

Laravel menyajikan satu halaman Inertia yang menyatukan Explorer, editor, Tanya dan Agent.

## Input

Permintaan GET /ide dari sesi aktif, props server, aset Vue hasil Vite dan pilihan browser.

## Output

Halaman IDE, panel terpilih, status editor dan notifikasi.

## Alur kerja

1. composer run dev menjalankan server, queue listener dan Vite.
2. Router memanggil controller IDE, Inertia menyerahkan props ke Vue, lalu halaman menyediakan API dan state bersama ke komponennya.
3. URL yang tidak terdaftar mendapatkan halaman 404.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [composer.json](../../composer.json) | Dependensi PHP dan skrip startup. |
| [composer.lock](../../composer.lock) | Versi paket PHP terkunci. |
| [package.json](../../package.json) | Dependensi frontend dan perintah build. |
| [package-lock.json](../../package-lock.json) | Versi dependensi npm terkunci. |
| [vite.config.ts](../../vite.config.ts) | Konfigurasi bundling dan plugin. |
| [artisan](../../artisan) | Entry point CLI Laravel. |
| [public/index.php](../../public/index.php) | Entry point web. |
| [bootstrap/app.php](../../bootstrap/app.php) | Konfigurasi aplikasi dan registrasi rute. |
| [routes/web.php](../../routes/web.php) | Rute web, autentikasi dan API IDE. |
| [app/Http/Controllers/AgentIdeController.php](../../app/Http/Controllers/AgentIdeController.php) | Menyiapkan props IDE. |
| [app/Http/Middleware/HandleInertiaRequests.php](../../app/Http/Middleware/HandleInertiaRequests.php) | Membagikan data server ke halaman. |
| [resources/js/app.ts](../../resources/js/app.ts) | Bootstrap Vue dan resolver halaman. |
| [resources/views/app.blade.php](../../resources/views/app.blade.php) | Dokumen HTML utama. |
| [resources/js/pages/AgentIdeApp.vue](../../resources/js/pages/AgentIdeApp.vue) | Komposisi panel dan pergantian mode. |
| [resources/js/composables/useIdeState.ts](../../resources/js/composables/useIdeState.ts) | State tab, editor dan notifikasi. |
| [resources/js/composables/useIdeApi.ts](../../resources/js/composables/useIdeApi.ts) | Klien HTTP IDE. |
| [resources/js/components/agent-ide/HeaderNav.vue](../../resources/js/components/agent-ide/HeaderNav.vue) | Perintah navigasi, simpan dan jalankan. |
| [resources/js/components/agent-ide/ToastStack.vue](../../resources/js/components/agent-ide/ToastStack.vue) | Umpan balik operasi. |
| [resources/css/app.css](../../resources/css/app.css) | Style aplikasi. |
| [resources/css/agent-ide.css](../../resources/css/agent-ide.css) | Style IDE. |
| [resources/views/errors/404.blade.php](../../resources/views/errors/404.blade.php) | Halaman saat URL tidak dikenal. |
| [resources/js/pages/Welcome.vue](../../resources/js/pages/Welcome.vue) | Halaman sambutan tanpa rute web tersendiri. |
| [resources/js/pages/Calculator.vue](../../resources/js/pages/Calculator.vue) | Halaman kalkulator tanpa rute web tersendiri. |
| [deploy.sh](../../deploy.sh) | Deployment dari main, instalasi, build, migrasi dan cache. |
| [bootstrap/providers.php](../../bootstrap/providers.php) | Registrasi service provider. |
| [app/Providers/AppServiceProvider.php](../../app/Providers/AppServiceProvider.php) | Hook konfigurasi aplikasi. |
| [config/app.php](../../config/app.php) | Nama, environment dan konfigurasi inti. |
| [config/database.php](../../config/database.php) | Koneksi database untuk tabel aplikasi. |
| [config/logging.php](../../config/logging.php) | Channel log server. |
| [config/filesystems.php](../../config/filesystems.php) | Definisi disk filesystem. |
| [app/Http/Controllers/Controller.php](../../app/Http/Controllers/Controller.php) | Kelas dasar controller. |
| [resources/views/components/layout.blade.php](../../resources/views/components/layout.blade.php) | Layout Blade bersama. |
| [tsconfig.json](../../tsconfig.json) | Opsi kompilasi TypeScript. |
| [phpunit.xml](../../phpunit.xml) | Environment dan suite PHPUnit. |
| [tests/Pest.php](../../tests/Pest.php) | Bootstrap suite Pest. |
| [tests/TestCase.php](../../tests/TestCase.php) | Basis pengujian Laravel. |
| [README.md](../../README.md) | Petunjuk proyek. |
| [PETA_FITUR.md](../../PETA_FITUR.md) | Peta awal kluster dan relasi file. |

## Metode publik pada implementasi terkait

**AgentIdeController.php**

- `index()`

**HandleInertiaRequests.php**

- `version(Request $request) → ?string`
- `share(Request $request) → array`

**AppServiceProvider.php**

- `register() → void`
- `boot() → void`

## Navigasi dokumentasi

- [Registrasi, login, dan sesi pengguna](01-akses.md)
- [Pemilihan proyek dan kesiapan Agent](03-proyek.md)
