# Editor, tab, dan peninjauan diff

Editor memungkinkan pengguna mengubah buffer dan meninjau usulan sebelum menulis ke disk.

## Input

Path file, isi saat dibuka, buffer hasil edit, versi sebelum dan sesudah usulan.

## Output

Tab dengan status perubahan, isi tersimpan, atau tampilan perbandingan dua versi.

## Alur kerja

1. Explorer membuka file melalui API.
2. Editor memelihara isi di tab sampai aksi simpan.
3. Chat bisa membuka diff usulan.
4. Dalam review Agent, tombol Terapkan memakai endpoint run yang memeriksa verifikasi.
5. Setelah Apply halaman memperbarui tab terkait yang bersih.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [resources/js/components/agent-ide/EditorPane.vue](../../resources/js/components/agent-ide/EditorPane.vue) | Mengelola editor, buffer dan event simpan. |
| [resources/js/components/agent-ide/DiffViewer.vue](../../resources/js/components/agent-ide/DiffViewer.vue) | Perbandingan versi dan interaksi review. |
| [resources/js/composables/useIdeState.ts](../../resources/js/composables/useIdeState.ts) | Daftar tab serta tab aktif. |
| [resources/js/components/agent-ide/HeaderNav.vue](../../resources/js/components/agent-ide/HeaderNav.vue) | Memicu simpan atau eksekusi tab. |
| [resources/js/pages/AgentIdeApp.vue](../../resources/js/pages/AgentIdeApp.vue) | Menyegarkan tab setelah Apply. |
| [app/Http/Controllers/Api/AgentIde/FileController.php](../../app/Http/Controllers/Api/AgentIde/FileController.php) | Membaca dan menyimpan file di server. |

## Metode publik pada implementasi terkait

**FileController.php**

- `tree(Request $request)`
- `read(Request $request)`
- `save(Request $request)`
- `create(Request $request)`
- `delete(Request $request)`
- `rename(Request $request)`
- `upload(Request $request)`

## Navigasi dokumentasi

- [Pengelolaan file dan folder](04-explorer.md)
- [Asisten Tanya dan pilihan provider](06-tanya.md)
