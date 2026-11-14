# PBKK-FP

Untuk mendaftarkan repo lokal, menjalankan worker, dan mencoba alur tugas hingga review patch, lihat [panduan coding harness](docs/HARNESS.md).

## Secure LLM Chat API Hub

API khusus tugas menggunakan Sanctum Bearer token, FormRequest, API Resource,
dan tabel `ai_chat_logs`. Antarmuka IDE lama tetap memakai rute web tersendiri.

### Instalasi backend

Membutuhkan PHP 8.3+ dan Composer. Setelah checkout perubahan ini, jalankan:

```sh
composer update laravel/sanctum --with-dependencies
php artisan migrate
php artisan test --filter=ChatAi
```

Commit `composer.lock` hasil update bersama perubahan kode. Dependency lock belum
diperbarui di lingkungan Codex karena PHP/Composer tidak tersedia.

Konfigurasi berikut ditaruh di `.env` lokal (jangan commit berkas tersebut):

```dotenv
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=qwen3:14b
SENOPATI_BASE_URL=https://senopati.its.ac.id
SENOPATI_API_KEY=
SENOPATI_MODEL=qwen3:14b
CHAT_AI_SYSTEM_PROMPT="Anda adalah asisten kode ITS. Bantu menulis dan meninjau kode serta rekomendasi keamanan dalam bahasa Indonesia."
```

Pastikan model Ollama tersedia (`ollama pull qwen3:14b`). URL Senopati harus
sesuai gateway yang diberikan ITS; implementasi menambahkan
`/llm-gateway/v1/chat/completions` ke base URL. Koneksi nyata Senopati perlu
diverifikasi dengan kredensial dan jaringan yang sesuai.

### Mendapatkan token

Buat akun melalui registrasi aplikasi, lalu jalankan `php artisan tinker`:

```php
$user = App\Models\User::where('email', 'anggota@example.com')->firstOrFail();
$user->createToken('demo', ['*'], now()->addDay())->plainTextToken;
```

Salin token yang hanya ditampilkan saat dibuat. Cabut token lewat
`$user->tokens()->delete()` setelah demo.

### API Contract

`POST /api/chat-ai`

Headers: `Authorization: Bearer <TOKEN>`, `Accept: application/json`,
`Content-Type: application/json`.

Body: `prompt` wajib string tidak kosong, maksimal 1000 karakter.
Provider, model, system prompt, dan API key ditentukan server.

```sh
curl -X POST http://localhost:8000/api/chat-ai \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"prompt":"Jelaskan middleware!"}'
```

Respons 200:

```json
{
  "status": "success",
  "data": {
    "session_id": 12,
    "model_used": "qwen3:14b",
    "prompt": "Jelaskan middleware!",
    "ai_response": "Middleware adalah...",
    "timestamp": "2026-08-28 09:30:00"
  }
}
```

`session_id` adalah ID rekaman satu pertukaran prompt-jawaban di `ai_chat_logs`,
bukan ID percakapan IDE. Timestamp memakai timezone aplikasi.
Log menyimpan user ID dari token, prompt, jawaban, model aktual, dan total token
dari provider (input + output). Jika provider tidak memberi usage, nilainya `null`;
angka token tidak diperkirakan. Respons provider gagal tidak dicatat sebagai chat sukses.

| HTTP | Arti |
| --- | --- |
| 401 | Token tidak ada, tidak valid, kedaluwarsa, atau dicabut |
| 422 | Validasi prompt gagal; detail pada `errors.prompt` |
| 502 | Provider menolak request atau mengirim jawaban kosong/tidak valid |
| 503 | Ollama tidak tersedia dan fallback tidak dikonfigurasi/tidak terhubung |

Contoh 401: `{"message":"Unauthenticated."}`.
Contoh 422: `{"message":"The prompt field is required.","errors":{"prompt":["The prompt field is required."]}}`.

### Challenge dan verifikasi

- System prompt bertema asisten kode dikirim sebagai pesan `system` terpisah.
- Fallback ke Senopati berjalan saat koneksi Ollama gagal atau Ollama mengembalikan
  5xx; 4xx tidak memicu fallback. Jawaban cadangan tetap berasal dari LLM.
- SSE streaming belum diimplementasikan.
- Tes fitur mencakup 401 tanpa token/token palsu, validasi, respons dan logging,
  system prompt, fallback, dan kegagalan provider. Tes memakai HTTP fake;
  koneksi LLM nyata harus didemokan terpisah.
- Pemeriksaan `git log --all -- .env` pada riwayat lokal yang tersedia tidak
  menemukan `.env`. Periksa juga remote/branch lain sebelum pengumpulan.

Panduan autentikasi: [dokumentasi resmi Sanctum](https://laravel.com/framework/docs/sanctum).

Alur demo 5 menit: kontrak API (30 detik), 401 tanpa token (45 detik), validasi
422 (45 detik), sukses dengan token dan log DB (90 detik), system prompt dan
fallback (60 detik), penutup (30 detik).

## Website Link (VPS Agung)

[http://172.188.98.77/](http://172.188.98.77/)

## Local Setup

```
git clone https://github.com/terainfinits/pbkk-fp
cd pbkk-fp
```

Setelah di folder kerja jalankan command ini
```
composer install
```
```
npm install
```
```
cp .env.example .env
```
```
php artisan key:generate
```
```
php artisan migrate
```
```
composer run dev
```

## Docker Setup on Windows

### Prerequisites
* **Operating System:** Windows 10 (64-bit: Pro, Enterprise, or Home build 19041+) or Windows 11.
* **Hardware Virtualization:** Enabled in your PC's BIOS/UEFI settings.

### Step 1: Download and Install Docker Desktop
1. Go to the official [Docker Desktop for Windows download page](https://www.docker.com/products/docker-desktop/).
2. Download the installer (`Docker Desktop Installer.exe`).
3. Run the installer. When prompted during installation:
   * Ensure **"Use WSL 2 instead of Hyper-V"** is checked (recommended).
4. Follow the installation wizard to complete the setup.
5. Once installed, start Docker Desktop from the Start menu.
6. `docker build -t sandbox-runner:latest .` on the root directory of the project to build the Docker image.

## Tangkapan Layar Tugas Minggu 9 PBKK

Menampilkan chat session lampau
<img width="1365" height="629" alt="image" src="https://github.com/user-attachments/assets/402c7359-8433-4686-a9d6-c4ae741128e8" />

Mencari chat session lampau berdasarkan kata kunci tertentu
<img width="1357" height="618" alt="image" src="https://github.com/user-attachments/assets/f68704c8-555d-4561-9a5d-af9a980a98c4" />

## Routes list
- `/` home page <br>
- `/agent/{tema?}` agent page <br>
- `/mahasiswa/{nrp 10 digit}` student profile page <br>
- `/hitung-ipk/{ip1?}/{ip2?}` ipk calculator <br>
- `/dashboard/mahasiswa/{nrp 10 digit}` student profile page but using dashboard prefix <br>
- `/dashboard/` home page but using dashboard prefix <br>

version pre-release
- `v0.0.0` base project Agung pbkk-tugas-2 
- `v0.1.0` connect to gemini 
- `v0.2.0` connect to ollama cloud 
- `v0.2.1` modularize file controller 
- `v0.3.0` add terminal feature, bug exist in terminal 
- `v0.3.1` fix terminal bug 
- `v0.4.0` change workspace directory, automatically write file to text editor after prompt and UI accept/reject changes and view diff; bug input terminal when running python with function input()

