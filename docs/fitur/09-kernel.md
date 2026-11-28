# Menjalankan kode dari editor

Pengguna memilih runtime dan menjalankan kode yang sedang diedit dari toolbar.

## Input

GET /ide/api/kernels untuk deteksi. POST /ide/api/code/run membawa code, language dan path.

## Output

Daftar runtime yang terdeteksi atau hasil eksekusi, termasuk output dan kesalahan.

## Alur kerja

1. Header mengirim event run-active.
2. Editor menyerahkan kode ke drawer terminal yang memanggil API kernel.
3. Controller memberikan root proyek ke service.
4. Service memilih runtime berdasarkan bahasa dan menjalankan proses.
5. Permintaan yang tidak dapat dijalankan mendapat error.
6. Service membuat file sementara di storage/app/kernel_runner, menjalankannya dari direktori proyek dan menghapus file sementara.
7. Python memakai python, JavaScript dan TypeScript dipetakan ke node, keluarga shell ke PowerShell, dan jalur default ke PHP.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Http/Controllers/Api/AgentIde/KernelController.php](../../app/Http/Controllers/Api/AgentIde/KernelController.php) | Endpoint deteksi dan eksekusi. |
| [app/Services/AgentIde/KernelRunnerService.php](../../app/Services/AgentIde/KernelRunnerService.php) | Mencari executable serta menjalankan kode. |
| [resources/js/components/agent-ide/TerminalDrawer.vue](../../resources/js/components/agent-ide/TerminalDrawer.vue) | Menampilkan hasil run. |
| [resources/js/components/agent-ide/EditorPane.vue](../../resources/js/components/agent-ide/EditorPane.vue) | Sumber kode dari tab aktif. |
| [tests/Feature/AgentIdeKernelTest.php](../../tests/Feature/AgentIdeKernelTest.php) | Tes kernel IDE. |

## Metode publik pada implementasi terkait

**KernelController.php**

- `index()`
- `run(Request $request)`

**KernelRunnerService.php**

- `detectKernels() → array`
- `run(string $code, string $language, string $filePath, string $basePath) → array`

## Skenario yang tercantum dalam tes

- ide page loads successfully
- kernels api detects python php node
- code runner executes python code in kernel
- code runner executes php code in kernel

## Navigasi dokumentasi

- [Terminal interaktif](08-terminal.md)
- [API chat bertoken dan log penggunaan](10-api-chat.md)
