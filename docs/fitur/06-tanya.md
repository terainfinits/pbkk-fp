# Asisten Tanya dan pilihan provider

Percakapan biasa mengaitkan pertanyaan dengan file yang sedang dilihat.

## Input

POST /ide/api/agent/prompt dengan prompt, provider, model, history, targetFile, targetDirectory, currentCode dan opsional sessionId atau apiKey.

## Output

Jawaban yang diformat untuk chat, usulan kode jika ada, penanda fallback atau pesan kegagalan.

## Alur kerja

1. Controller memeriksa prompt dan akses sesi.
2. Builder menyusun konteks proyek.
3. Service memilih adapter provider, formatter menerjemahkan hasil ke bentuk respons UI.
4. Pada jalur fallback yang diizinkan, generator lokal menghasilkan jawaban.
5. SettingsModal mengelola key provider yang dimasukkan di browser.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [resources/js/components/agent-ide/ChatPanel.vue](../../resources/js/components/agent-ide/ChatPanel.vue) | Form prompt, jawaban dan usulan perubahan. |
| [resources/js/components/agent-ide/SettingsModal.vue](../../resources/js/components/agent-ide/SettingsModal.vue) | Preferensi kredensial provider di localStorage. |
| [app/Http/Controllers/Api/AgentIde/ChatController.php](../../app/Http/Controllers/Api/AgentIde/ChatController.php) | Orkestrasi konteks, provider dan penyimpanan. |
| [app/Services/AgentIde/AgentContextBuilder.php](../../app/Services/AgentIde/AgentContextBuilder.php) | Menyusun konteks editor dan proyek. |
| [app/Services/AgentIde/LlmProviderService.php](../../app/Services/AgentIde/LlmProviderService.php) | Adapter HTTP serta pelaporan error. |
| [app/Services/AgentIde/FallbackAgentResponder.php](../../app/Services/AgentIde/FallbackAgentResponder.php) | Generator jawaban lokal. |
| [app/Services/AgentIde/AgentResponseFormatter.php](../../app/Services/AgentIde/AgentResponseFormatter.php) | Membentuk respons untuk UI. |
| [app/Services/SenopatiConnection.php](../../app/Services/SenopatiConnection.php) | Klasifikasi error transport. |
| [tests/Feature/IdePromptTest.php](../../tests/Feature/IdePromptTest.php) | Tes endpoint prompt. |
| [tests/Unit/AgentFilesTest.php](../../tests/Unit/AgentFilesTest.php) | Tes unit terkait keluaran agent. |

## Metode publik pada implementasi terkait

**ChatController.php**

- `prompt(Request $request)`

**AgentContextBuilder.php**

- `build(?string $targetDirectory, ?string $targetFile, ?string $currentCode) → string`

**LlmProviderService.php**

- `dispatch(string $provider, string $model, string $apiKey, string $systemContext, string $prompt, array $history = []) → array`

**FallbackAgentResponder.php**

- `generate(string $prompt, string $provider, string $model, ?string $targetFile, ?string $targetDirectory, ?string $apiNotice = null) → array`
- `index()`
- `store(Request $request)`
- `update(Request $request, $id)`
- `destroy($id)`
- `execute(string \$goal, array \$context = []) → array`

**AgentResponseFormatter.php**

- `format(string $rawText, string $provider, string $model, ?string $targetFile, ?string $targetDirectory, string $prompt = '') → array`

**SenopatiConnection.php**

- `describe(Throwable $exception) → string`

## Skenario yang tercantum dalam tes

- returns and stores the complete multi-file Senopati response through the IDE prompt route
- preserves all named files and the complete raw response

## Navigasi dokumentasi

- [Editor, tab, dan peninjauan diff](05-editor.md)
- [Riwayat dan ekspor percakapan Tanya](07-riwayat-chat.md)
