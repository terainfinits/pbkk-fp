# Registrasi, login, dan sesi pengguna

Pengunjung mengisi formulir registrasi atau login. Laravel memakai identitas sesi untuk membuka IDE dan menentukan pemilik proyek.

## Input

Nama, email, kata sandi dan konfirmasi saat registrasi. Login menerima kredensial melalui POST /login.

## Output

Akun tersimpan dan sesi terautentikasi, atau pesan validasi. Logout mengakhiri sesi.

## Alur kerja

1. Rute guest menampilkan formulir.
2. Controller memvalidasi input dan membuat atau mengautentikasi User.
3. Rute / mengarahkan pengguna sesuai status login.
4. POST /login dibatasi enam percobaan per menit.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Http/Controllers/AuthController.php](../../app/Http/Controllers/AuthController.php) | Mengendalikan formulir, autentikasi dan logout. |
| [app/Models/User.php](../../app/Models/User.php) | Model identitas Laravel dan pemilik data. |
| [resources/js/pages/Auth/Login.vue](../../resources/js/pages/Auth/Login.vue) | Formulir masuk. |
| [resources/js/pages/Auth/Register.vue](../../resources/js/pages/Auth/Register.vue) | Formulir pendaftaran. |
| [config/auth.php](../../config/auth.php) | Guard dan provider pengguna. |
| [config/session.php](../../config/session.php) | Penyimpanan dan masa hidup sesi. |
| [database/migrations/0001_01_01_000000_create_users_table.php](../../database/migrations/0001_01_01_000000_create_users_table.php) | Skema pengguna dan sesi. |
| [database/factories/UserFactory.php](../../database/factories/UserFactory.php) | Akun uji. |
| [database/seeders/DatabaseSeeder.php](../../database/seeders/DatabaseSeeder.php) | Data awal. |
| [tests/Feature/AuthTest.php](../../tests/Feature/AuthTest.php) | Pengujian akses dan autentikasi. |

## Metode publik pada implementasi terkait

**AuthController.php**

- `showLogin() → Response`
- `showRegister() → Response`
- `login(Request $request) → RedirectResponse`
- `register(Request $request) → RedirectResponse`
- `logout(Request $request) → RedirectResponse`

**UserFactory.php**

- `definition() → array`
- `unverified() → static`

**DatabaseSeeder.php**

- `run() → void`

## Skenario yang tercantum dalam tes

- guest sees auth pages and cannot access the ide or its api
- registration signs in and redirects to the ide
- login rejects wrong credentials and logout ends the session
- chat sessions are only accessible to their owner

## Navigasi dokumentasi

- [Startup dan navigasi IDE](02-halaman-ide.md)
