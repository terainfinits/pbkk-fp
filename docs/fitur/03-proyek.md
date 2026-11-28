# Pemilihan proyek dan kesiapan Agent

Satu pilihan menentukan folder kerja Explorer, konteks Tanya, dan workspace Agent.

## Input

ID proyek ide atau ID workspace pengguna. Pendaftaran CLI menerima path, email, image serta opsi check.

## Output

Daftar proyek beserta nama, workspace dan alasan bila belum siap. Pilihan terakhir tersimpan per akun di browser.

## Alur kerja

1. Projects memetakan ID ke direktori di server dan memeriksa kepemilikan.
2. Folder IDE digabung dengan entri workspace jika path fisiknya sama.
3. Repo biasa memerlukan HEAD bersih.
4. Folder IDE memakai kesiapan Git, akses filesystem serta runner Docker.
5. Saat pindah dengan tab kotor, pengguna dapat menyimpan, membuang atau membatalkan.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Services/AgentIde/Projects.php](../../app/Services/AgentIde/Projects.php) | Resolusi proyek, daftar dan pendaftaran Folder IDE. |
| [app/Console/Commands/RegisterWorkspace.php](../../app/Console/Commands/RegisterWorkspace.php) | Mendaftarkan repo serta image tes. |
| [resources/js/pages/AgentIdeApp.vue](../../resources/js/pages/AgentIdeApp.vue) | Pemilih proyek, konfirmasi tab kotor dan pemulihan pilihan. |
| [tests/Feature/IdeProjectsTest.php](../../tests/Feature/IdeProjectsTest.php) | Menguji isolasi proyek dan prasyarat. |
| [config/services.php](../../config/services.php) | Konfigurasi runner IDE serta koneksi provider. |

## Metode publik pada implementasi terkait

**Projects.php**

- `current() → ?object`
- `reason(string $path, ?string $image) → ?string`
- `listing() → array`
- `prepare()`
- `folderWorkspace() → int`

**RegisterWorkspace.php**

- `handle(Repository $repo) → int`

## Skenario yang tercantum dalam tes

- isolates identical files and chat histories by owned project
- explains each Git and runner prerequisite

## Navigasi dokumentasi

- [Startup dan navigasi IDE](02-halaman-ide.md)
- [Pengelolaan file dan folder](04-explorer.md)
