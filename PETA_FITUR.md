# Peta fitur PBKK-FP

Aplikasi ini menyediakan ruang kerja pemrograman di browser. Setelah login, pengguna bisa membuka file, mengedit kode, meminta saran AI, meninjau perubahan, dan menjalankan kode dari halaman `/ide`. Percakapan tersimpan agar pengguna bisa melanjutkannya kemudian.

Klien lain juga bisa mengirim pertanyaan langsung ke Laravel melalui `POST /api/chat-ai` dengan token Sanctum. Endpoint ini dirancang untuk antarmuka Livewire pada pertemuan berikutnya. Antarmuka IDE yang sudah ada menggunakan Vue dan Inertia.

| Fitur | File utama | Hubungan dengan fitur lain |
|---|---|---|
| **Login dan registrasi** | [Login.vue](resources/js/pages/Auth/Login.vue), [Register.vue](resources/js/pages/Auth/Register.vue), [AuthController.php](app/Http/Controllers/AuthController.php), [User.php](app/Models/User.php), [web.php](routes/web.php) | Login membuka akses ke `/ide`. Model User juga menjadi pemilik token API. |
| **Halaman IDE** | [AgentIdeController.php](app/Http/Controllers/AgentIdeController.php), [AgentIdeApp.vue](resources/js/pages/AgentIdeApp.vue), [useIdeState.ts](resources/js/composables/useIdeState.ts), [useIdeApi.ts](resources/js/composables/useIdeApi.ts) | Halaman menyatukan explorer, editor, chat, dan terminal. Panel berbagi status seperti tab aktif dan memakai klien HTTP IDE. |
| **Kelola file** | [SidebarExplorer.vue](resources/js/components/agent-ide/SidebarExplorer.vue), [TreeNode.vue](resources/js/components/agent-ide/TreeNode.vue), [FileController.php](app/Http/Controllers/Api/AgentIde/FileController.php), [FileExplorerService.php](app/Services/AgentIde/FileExplorerService.php) | Explorer menampilkan pohon file. File yang dibuka masuk ke editor. Operasi buat, unggah, ubah nama, dan hapus melewati FileController. |
| **Edit dan tinjau perubahan** | [EditorPane.vue](resources/js/components/agent-ide/EditorPane.vue), [DiffViewer.vue](resources/js/components/agent-ide/DiffViewer.vue), [ChatPanel.vue](resources/js/components/agent-ide/ChatPanel.vue), [FileController.php](app/Http/Controllers/Api/AgentIde/FileController.php) | Editor membuka dan menyimpan isi file. Usulan kode dari chat dapat dibandingkan dengan isi lama sebelum diterapkan. |
| **Chat asisten di IDE** | [ChatPanel.vue](resources/js/components/agent-ide/ChatPanel.vue), [ChatController.php](app/Http/Controllers/Api/AgentIde/ChatController.php), [AgentContextBuilder.php](app/Services/AgentIde/AgentContextBuilder.php), [LlmProviderService.php](app/Services/AgentIde/LlmProviderService.php), [AgentResponseFormatter.php](app/Services/AgentIde/AgentResponseFormatter.php), [FallbackAgentResponder.php](app/Services/AgentIde/FallbackAgentResponder.php) | Panel mengirim prompt dan konteks editor ke `/ide/api/agent/prompt`. Controller memilih provider atau respons cadangan, lalu menyimpan percakapan. |
| **Riwayat chat IDE** | [ChatPanel.vue](resources/js/components/agent-ide/ChatPanel.vue), [ChatController.php](app/Http/Controllers/Api/AgentIde/ChatController.php), [ChatSessionController.php](app/Http/Controllers/Api/AgentIde/ChatSessionController.php), [ChatSession.php](app/Models/ChatSession.php), [ChatMessage.php](app/Models/ChatMessage.php) | ChatController menulis pesan ke sesi pengguna. ChatSessionController melayani daftar, pencarian, buka kembali, ubah, hapus, dan ekspor sesi. |
| **Terminal dan eksekusi kode** | [TerminalDrawer.vue](resources/js/components/agent-ide/TerminalDrawer.vue), [HeaderNav.vue](resources/js/components/agent-ide/HeaderNav.vue), [TerminalController.php](app/Http/Controllers/Api/AgentIde/TerminalController.php), [TerminalService.php](app/Services/AgentIde/TerminalService.php), [KernelController.php](app/Http/Controllers/Api/AgentIde/KernelController.php), [KernelRunnerService.php](app/Services/AgentIde/KernelRunnerService.php) | Perintah terminal masuk ke `/ide/api/terminal/execute`. Aksi menjalankan kode memakai `/ide/api/code/run`. Hasilnya dapat dilihat di panel terminal. |
| **Chat melalui API bertoken** | [api.php](routes/api.php), [User.php](app/Models/User.php), [ChatAiRequest.php](app/Http/Requests/ChatAiRequest.php), [ChatAiController.php](app/Http/Controllers/Api/ChatAiController.php), [ChatAiService.php](app/Services/ChatAiService.php), [ChatAiResource.php](app/Http/Resources/ChatAiResource.php) | Klien mengirim Bearer token dan prompt ke `/api/chat-ai`. Sanctum memeriksa token, Form Request memvalidasi teks, lalu service meminta jawaban Ollama dengan fallback Senopati. Resource membentuk JSON untuk klien. |
| **Log penggunaan API** | [ChatAiController.php](app/Http/Controllers/Api/ChatAiController.php), [AiChatLog.php](app/Models/AiChatLog.php), [2026_11_13_000002_create_ai_chat_logs_table.php](database/migrations/2026_11_13_000002_create_ai_chat_logs_table.php) | Setiap jawaban sukses disimpan bersama user, prompt, model, dan jumlah token jika tersedia. ID log dikembalikan sebagai `session_id`. Riwayat IDE memakai tabel sesi dan pesan tersendiri. |


## Harness tugas coding

Dari halaman `/ide`, tombol **Tugas coding** membuka [HarnessPanel.vue](resources/js/components/agent-ide/HarnessPanel.vue). Daftarkan repo Git dan image tes dengan `php artisan harness:register`. Panel mengirim tugas ke [rute harness](routes/harness.php). [HarnessController.php](app/Http/Controllers/HarnessController.php) membuat run dan mengantrekannya pada queue `harness`.

[RunCodingAgent.php](app/Jobs/RunCodingAgent.php) meminta langkah berikutnya dari Senopati, memakai worktree Git untuk perubahan, dan memanggil runner Docker untuk tes. Panel mengambil event dan hasil run secara berkala. Pengguna dapat membatalkan run atau meninjau diff lalu menerima atau menolak patch. Scheduler memulihkan run yang terhenti. Petunjuk menjalankan worker dan contoh image tersedia di [panduan harness](docs/HARNESS.md).

## Menelusuri koneksi dari antarmuka

| Mulai dari | Alur yang bisa diikuti |
|---|---|
| Chat samping | `ChatPanel.vue` mengirim prompt lewat `useIdeApi.ts`. Rute web meneruskannya ke `ChatController.php`. Untuk meninjau usulan, panel mengirim event `ide:open-diff` ke `EditorPane.vue`, yang menampilkan `DiffViewer.vue`. |
| Editor teks | `HeaderNav.vue` mengirim `ide:save-active` ke `EditorPane.vue`. Editor menyimpan tab aktif lewat klien API, rute web, `FileController.php`, dan `FileExplorerService.php`. |
| Jalankan kode | `HeaderNav.vue` memicu `ide:run-active`. Editor mengirim `ide:kernel-run` ke `TerminalDrawer.vue`, yang meminta eksekusi lewat API kernel. Panel yang sama mengirim perintah terminal ke endpoint terminal. |
| Review tugas coding | `HarnessPanel.vue` mengambil perubahan dari endpoint run dan membuka `DiffViewer.vue` dalam mode review. Tombol apply meminta `HarnessController.php` menerapkan patch lewat `Repository.php`. |

## Indeks file per kluster

Setiap file memiliki satu kluster utama. Komentar `connect with` memakai path relatif terhadap file tersebut dan mencatat relasi dua arah seperti import, event, rute HTTP, skema data, konfigurasi, pengujian atau panduan runner. Komponen bersama seperti diff viewer dapat digunakan dari beberapa fitur.

### Halaman IDE dan navigasi

- [resources/js/app.ts](resources/js/app.ts)
- [resources/css/app.css](resources/css/app.css)
- [resources/css/agent-ide.css](resources/css/agent-ide.css)
- [resources/js/pages/AgentIdeApp.vue](resources/js/pages/AgentIdeApp.vue)
- [app/Http/Controllers/AgentIdeController.php](app/Http/Controllers/AgentIdeController.php)
- [app/Http/Middleware/HandleInertiaRequests.php](app/Http/Middleware/HandleInertiaRequests.php)
- [resources/js/components/agent-ide/HeaderNav.vue](resources/js/components/agent-ide/HeaderNav.vue)
- [resources/js/components/agent-ide/ToastStack.vue](resources/js/components/agent-ide/ToastStack.vue)
- [resources/views/app.blade.php](resources/views/app.blade.php)
- [resources/js/composables/useIdeApi.ts](resources/js/composables/useIdeApi.ts)
- [resources/js/composables/useIdeState.ts](resources/js/composables/useIdeState.ts)
- [routes/web.php](routes/web.php)
- [bootstrap/app.php](bootstrap/app.php)
- [app/Http/Controllers/Controller.php](app/Http/Controllers/Controller.php)
- [resources/views/components/layout.blade.php](resources/views/components/layout.blade.php)
- [resources/views/errors/404.blade.php](resources/views/errors/404.blade.php)
- [tests/Feature/ExampleTest.php](tests/Feature/ExampleTest.php)

### Login, registrasi dan akses pengguna

- [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php)
- [resources/js/pages/Auth/Login.vue](resources/js/pages/Auth/Login.vue)
- [resources/js/pages/Auth/Register.vue](resources/js/pages/Auth/Register.vue)
- [app/Models/User.php](app/Models/User.php)
- [database/factories/UserFactory.php](database/factories/UserFactory.php)
- [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)
- [database/migrations/0001_01_01_000000_create_users_table.php](database/migrations/0001_01_01_000000_create_users_table.php)
- [tests/Feature/AuthTest.php](tests/Feature/AuthTest.php)
- [config/auth.php](config/auth.php)

### Explorer, pembuatan dan unggah file

- [resources/js/components/agent-ide/SidebarExplorer.vue](resources/js/components/agent-ide/SidebarExplorer.vue)
- [resources/js/components/agent-ide/TreeNode.vue](resources/js/components/agent-ide/TreeNode.vue)
- [resources/js/components/agent-ide/CreateModal.vue](resources/js/components/agent-ide/CreateModal.vue)
- [resources/js/components/agent-ide/FileDropZone.vue](resources/js/components/agent-ide/FileDropZone.vue)
- [app/Http/Controllers/Api/AgentIde/FileController.php](app/Http/Controllers/Api/AgentIde/FileController.php)
- [app/Services/AgentIde/FileExplorerService.php](app/Services/AgentIde/FileExplorerService.php)

### Editor teks dan peninjauan diff

- [resources/js/components/agent-ide/EditorPane.vue](resources/js/components/agent-ide/EditorPane.vue)
- [resources/js/components/agent-ide/DiffViewer.vue](resources/js/components/agent-ide/DiffViewer.vue)

### Panel chat dan pengaturan model

- [resources/js/components/agent-ide/ChatPanel.vue](resources/js/components/agent-ide/ChatPanel.vue)
- [resources/js/components/agent-ide/SettingsModal.vue](resources/js/components/agent-ide/SettingsModal.vue)
- [app/Http/Controllers/Api/AgentIde/ChatController.php](app/Http/Controllers/Api/AgentIde/ChatController.php)
- [app/Services/AgentIde/AgentContextBuilder.php](app/Services/AgentIde/AgentContextBuilder.php)
- [app/Services/AgentIde/AgentResponseFormatter.php](app/Services/AgentIde/AgentResponseFormatter.php)
- [app/Services/AgentIde/FallbackAgentResponder.php](app/Services/AgentIde/FallbackAgentResponder.php)
- [app/Services/AgentIde/LlmProviderService.php](app/Services/AgentIde/LlmProviderService.php)
- [tests/Feature/IdePromptTest.php](tests/Feature/IdePromptTest.php)
- [tests/Unit/AgentFilesTest.php](tests/Unit/AgentFilesTest.php)
- [config/services.php](config/services.php)

### Riwayat dan ekspor percakapan

- [app/Http/Controllers/Api/AgentIde/ChatSessionController.php](app/Http/Controllers/Api/AgentIde/ChatSessionController.php)
- [app/Models/ChatSession.php](app/Models/ChatSession.php)
- [app/Models/ChatMessage.php](app/Models/ChatMessage.php)
- [database/migrations/2026_10_18_100000_create_chat_storage_tables.php](database/migrations/2026_10_18_100000_create_chat_storage_tables.php)
- [database/migrations/2026_10_19_000000_add_user_id_to_chat_sessions_table.php](database/migrations/2026_10_19_000000_add_user_id_to_chat_sessions_table.php)
- [tests/Feature/ChatStorageTest.php](tests/Feature/ChatStorageTest.php)

### Terminal dan eksekusi kode

- [resources/js/components/agent-ide/TerminalDrawer.vue](resources/js/components/agent-ide/TerminalDrawer.vue)
- [app/Http/Controllers/Api/AgentIde/TerminalController.php](app/Http/Controllers/Api/AgentIde/TerminalController.php)
- [app/Services/AgentIde/TerminalService.php](app/Services/AgentIde/TerminalService.php)
- [app/Http/Controllers/Api/AgentIde/KernelController.php](app/Http/Controllers/Api/AgentIde/KernelController.php)
- [app/Services/AgentIde/KernelRunnerService.php](app/Services/AgentIde/KernelRunnerService.php)
- [tests/Feature/AgentIdeKernelTest.php](tests/Feature/AgentIdeKernelTest.php)

### API chat bertoken dan log penggunaan

- [routes/api.php](routes/api.php)
- [app/Http/Controllers/Api/ChatAiController.php](app/Http/Controllers/Api/ChatAiController.php)
- [app/Http/Requests/ChatAiRequest.php](app/Http/Requests/ChatAiRequest.php)
- [app/Http/Resources/ChatAiResource.php](app/Http/Resources/ChatAiResource.php)
- [app/Services/ChatAiService.php](app/Services/ChatAiService.php)
- [app/Models/AiChatLog.php](app/Models/AiChatLog.php)
- [database/migrations/2026_11_13_000001_create_personal_access_tokens_table.php](database/migrations/2026_11_13_000001_create_personal_access_tokens_table.php)
- [database/migrations/2026_11_13_000002_create_ai_chat_logs_table.php](database/migrations/2026_11_13_000002_create_ai_chat_logs_table.php)
- [config/chat_ai.php](config/chat_ai.php)
- [tests/Feature/ChatAiTest.php](tests/Feature/ChatAiTest.php)

### Harness tugas coding

- [resources/js/components/agent-ide/HarnessPanel.vue](resources/js/components/agent-ide/HarnessPanel.vue)
- [routes/harness.php](routes/harness.php)
- [app/Http/Controllers/HarnessController.php](app/Http/Controllers/HarnessController.php)
- [app/Console/Commands/RegisterWorkspace.php](app/Console/Commands/RegisterWorkspace.php)
- [app/Jobs/RunCodingAgent.php](app/Jobs/RunCodingAgent.php)
- [app/Services/Harness/Repository.php](app/Services/Harness/Repository.php)
- [app/Services/Harness/SenopatiAgent.php](app/Services/Harness/SenopatiAgent.php)
- [app/Services/Harness/Tools.php](app/Services/Harness/Tools.php)
- [routes/console.php](routes/console.php)
- [docker/harness/Dockerfile](docker/harness/Dockerfile)
- [database/migrations/2026_11_14_000000_create_harness_tables.php](database/migrations/2026_11_14_000000_create_harness_tables.php)
- [database/migrations/0001_01_01_000002_create_jobs_table.php](database/migrations/0001_01_01_000002_create_jobs_table.php)
- [config/queue.php](config/queue.php)
- [tests/Feature/HarnessTest.php](tests/Feature/HarnessTest.php)
- [tests/Feature/LiveHarnessTest.php](tests/Feature/LiveHarnessTest.php)

### Halaman Vue tanpa rute terdaftar

Resolver Inertia mengenali halaman kalkulator dan halaman sambutan, tetapi belum ada rute Laravel yang membukanya.

- [resources/js/pages/Calculator.vue](resources/js/pages/Calculator.vue)
- [resources/js/pages/Welcome.vue](resources/js/pages/Welcome.vue)

### Contoh keluaran workspace

File contoh yang tercatat di Git mencakup kalkulator CLI, Hello World, fungsi bilangan prima dalam `fibonacci.py`, contoh controller, serta contoh analisis data. `folder-generated/app/Services/GeneratedCode.php` berisi Python. File `app/Services/GeneratedCode.php` di luar folder contoh berisi teks silsilah dan tidak termasuk service aplikasi.

- [folder-generated/app/Examples/fibonacci.py](folder-generated/app/Examples/fibonacci.py)
- [folder-generated/app/Http/Controllers/UserController.php](folder-generated/app/Http/Controllers/UserController.php)
- [folder-generated/app/Services/GeneratedCode.php](folder-generated/app/Services/GeneratedCode.php)
- [folder-generated/calculator.py](folder-generated/calculator.py)
- [folder-generated/data_loader.py](folder-generated/data_loader.py)
- [folder-generated/hello_world.py](folder-generated/hello_world.py)
- [folder-generated/pipeline_data_analyst.py](folder-generated/pipeline_data_analyst.py)
