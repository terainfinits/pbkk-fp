# Terminal interaktif

Pengguna menjalankan perintah manual dari drawer IDE.

## Input

POST /ide/api/terminal/execute dengan command dan cwd relatif.

## Output

Output standar, kesalahan, exit code, waktu dan direktori kerja berikutnya.

## Alur kerja

1. Service menyelesaikan direktori awal.
2. Perintah cd memperbarui cwd dan clear memberi sinyal untuk mengosongkan tampilan.
3. Perintah lain dijalankan melalui shell pada host.
4. Pembatasan direktori awal tidak mengisolasi isi perintah seperti container tes.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [resources/js/components/agent-ide/TerminalDrawer.vue](../../resources/js/components/agent-ide/TerminalDrawer.vue) | Input perintah dan tampilan keluaran. |
| [app/Http/Controllers/Api/AgentIde/TerminalController.php](../../app/Http/Controllers/Api/AgentIde/TerminalController.php) | Validasi perintah dan serialisasi hasil. |
| [app/Services/AgentIde/TerminalService.php](../../app/Services/AgentIde/TerminalService.php) | Resolusi cwd dan peluncuran proses. |

## Metode publik pada implementasi terkait

**TerminalController.php**

- `execute(Request $request) → JsonResponse`

**TerminalService.php**

- `execute(string $command, string $cwd = '') → array`

## Navigasi dokumentasi

- [Riwayat dan ekspor percakapan Tanya](07-riwayat-chat.md)
- [Menjalankan kode dari editor](09-kernel.md)
