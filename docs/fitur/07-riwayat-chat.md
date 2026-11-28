# Riwayat dan ekspor percakapan Tanya

Pengguna membuka, menamai ulang, menghapus atau mengekspor sesi percakapan per proyek.

## Input

Sesi pengguna, proyek, id chat, judul dan parameter daftar. Pesan berasal dari percakapan Tanya.

## Output

Daftar sesi, detail pesan, judul baru, konfirmasi penghapusan atau unduhan Markdown.

## Alur kerja

1. Rute /ide/api/chats melayani daftar dan pembuatan.
2. Rute dengan ID membaca, memperbarui atau menghapus.
3. GET /ide/api/chats/{id}/export merangkai pesan menjadi Markdown.
4. Query membatasi akses berdasarkan pengguna dan proyek.
5. Sesi lama tanpa proyek tersedia melalui Folder IDE.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Http/Controllers/Api/AgentIde/ChatSessionController.php](../../app/Http/Controllers/Api/AgentIde/ChatSessionController.php) | Operasi dan ekspor sesi. |
| [app/Models/ChatSession.php](../../app/Models/ChatSession.php) | Model sesi dan relasi pesan. |
| [app/Models/ChatMessage.php](../../app/Models/ChatMessage.php) | Model pesan. |
| [app/Http/Controllers/Api/AgentIde/ChatController.php](../../app/Http/Controllers/Api/AgentIde/ChatController.php) | Mencatat percakapan. |
| [database/migrations/2026_10_18_100000_create_chat_storage_tables.php](../../database/migrations/2026_10_18_100000_create_chat_storage_tables.php) | Skema sesi dan pesan. |
| [database/migrations/2026_10_19_000000_add_user_id_to_chat_sessions_table.php](../../database/migrations/2026_10_19_000000_add_user_id_to_chat_sessions_table.php) | Kepemilikan sesi. |
| [database/migrations/2026_11_25_020000_scope_chat_projects.php](../../database/migrations/2026_11_25_020000_scope_chat_projects.php) | Lingkup proyek pada chat. |
| [tests/Feature/ChatStorageTest.php](../../tests/Feature/ChatStorageTest.php) | Pengujian penyimpanan dan ekspor. |
| [resources/js/components/agent-ide/ChatPanel.vue](../../resources/js/components/agent-ide/ChatPanel.vue) | Daftar dan pemulihan sesi. |

## Metode publik pada implementasi terkait

**ChatSessionController.php**

- `index(Request $request) → JsonResponse`
- `store(Request $request) → JsonResponse`
- `show(Request $request, $id) → JsonResponse`
- `export(Request $request, $id)`
- `update(Request $request, $id) → JsonResponse`
- `destroy(Request $request, $id) → JsonResponse`

**ChatSession.php**

- `messages() → HasMany`

**ChatMessage.php**

- `session() → BelongsTo`

**ChatController.php**

- `prompt(Request $request)`

## Skenario yang tercantum dalam tes

- can create, list, and view chat sessions
- agent prompt stores user and assistant messages in database
- can delete a chat session and cascade delete its messages
- can search and filter chat sessions by keyword in title or message content
- can export chat session as markdown document

## Navigasi dokumentasi

- [Asisten Tanya dan pilihan provider](06-tanya.md)
- [Terminal interaktif](08-terminal.md)
