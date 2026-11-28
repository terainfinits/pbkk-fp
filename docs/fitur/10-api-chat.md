# API chat bertoken dan log penggunaan

Klien API mengirim satu prompt melalui Laravel Sanctum.

## Input

POST /api/chat-ai dengan Bearer token yang sah dan prompt string wajib, maksimum 1000 karakter.

## Output

JSON dengan session_id, model_used, prompt, ai_response dan timestamp. session_id merujuk ke log jawaban.

## Alur kerja

1. Middleware Sanctum mengautentikasi klien.
2. Request memvalidasi prompt, service mencoba Ollama lalu jalur fallback Senopati, controller mencatat jawaban sukses dan resource memformat JSON.
3. Tabel ai_chat_logs menyimpan penggunaan terpisah dari sesi chat IDE.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [routes/api.php](../../routes/api.php) | Registrasi endpoint Sanctum. |
| [app/Http/Requests/ChatAiRequest.php](../../app/Http/Requests/ChatAiRequest.php) | Otorisasi dan validasi input. |
| [app/Http/Controllers/Api/ChatAiController.php](../../app/Http/Controllers/Api/ChatAiController.php) | Menangani respons dan menyimpan log. |
| [app/Services/ChatAiService.php](../../app/Services/ChatAiService.php) | Permintaan model dan fallback. |
| [app/Http/Resources/ChatAiResource.php](../../app/Http/Resources/ChatAiResource.php) | Kontrak JSON. |
| [app/Models/AiChatLog.php](../../app/Models/AiChatLog.php) | Model log penggunaan. |
| [config/chat_ai.php](../../config/chat_ai.php) | Konfigurasi provider dan model. |
| [database/migrations/2026_11_13_000001_create_personal_access_tokens_table.php](../../database/migrations/2026_11_13_000001_create_personal_access_tokens_table.php) | Skema token Sanctum. |
| [database/migrations/2026_11_13_000002_create_ai_chat_logs_table.php](../../database/migrations/2026_11_13_000002_create_ai_chat_logs_table.php) | Skema log. |
| [tests/Feature/ChatAiTest.php](../../tests/Feature/ChatAiTest.php) | Tes API bertoken. |

## Metode publik pada implementasi terkait

**ChatAiRequest.php**

- `authorize() → bool`
- `rules() → array`

**ChatAiController.php**

- `__invoke(ChatAiRequest $request, ChatAiService $service) → JsonResponse`

**ChatAiService.php**

- `reply(string $prompt) → array`

**ChatAiResource.php**

- `toArray(Request $request) → array`

## Skenario yang tercantum dalam tes

- rejects requests without a token before contacting the provider
- rejects invalid bearer tokens
- validates the prompt before contacting the provider
- returns the contract and records the authenticated user and real usage
- falls back to Senopati when Ollama is offline
- does not log successful chats when both providers fail

## Navigasi dokumentasi

- [Menjalankan kode dari editor](09-kernel.md)
- [Memulai tugas dan menjalankan worker otomatis](11-antrean.md)
