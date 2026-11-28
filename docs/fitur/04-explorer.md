# Pengelolaan file dan folder

Explorer mengubah isi proyek aktif melalui API file.

## Input

ID proyek, path relatif, direktori, jenis item, konten, newName atau unggahan multipart.

## Output

Pohon direktori, isi file, path baru, konfirmasi operasi atau kesalahan akses dan konflik nama.

## Alur kerja

1. Klien mengambil pohon lewat GET /ide/api/tree.
2. Operasi read, save, create, rename, delete dan upload dikirim ke POST /ide/api/file/*.
3. Service menyelesaikan path terhadap root proyek sebelum melakukan operasi disk.
4. UI menyegarkan cabang yang terdampak.
5. Sebagai contoh, folder-generated/subway-surfer-game berisi index.html, src/index.js dan package.json milik proyek pengguna.
6. Manifest ini menyediakan skrip start dan dependensi Phaser.
7. File semacam ini merupakan input Explorer dan snapshot, sehingga isinya bisa berubah setelah Apply.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [resources/js/components/agent-ide/SidebarExplorer.vue](../../resources/js/components/agent-ide/SidebarExplorer.vue) | Memuat pohon dan menangani aksi pengguna. |
| [resources/js/components/agent-ide/TreeNode.vue](../../resources/js/components/agent-ide/TreeNode.vue) | Merender node direktori dan file. |
| [resources/js/components/agent-ide/CreateModal.vue](../../resources/js/components/agent-ide/CreateModal.vue) | Input nama dan jenis item. |
| [resources/js/components/agent-ide/FileDropZone.vue](../../resources/js/components/agent-ide/FileDropZone.vue) | Menerima unggahan dari drag and drop. |
| [app/Http/Controllers/Api/AgentIde/FileController.php](../../app/Http/Controllers/Api/AgentIde/FileController.php) | Memetakan request dan hasil ke HTTP. |
| [app/Services/AgentIde/FileExplorerService.php](../../app/Services/AgentIde/FileExplorerService.php) | Operasi filesystem serta resolusi path. |
| [resources/js/composables/useIdeApi.ts](../../resources/js/composables/useIdeApi.ts) | Membentuk request proyek aktif. |
| [tests/Feature/IdeProjectsTest.php](../../tests/Feature/IdeProjectsTest.php) | Menguji file identik pada proyek berbeda. |

## Metode publik pada implementasi terkait

**FileController.php**

- `tree(Request $request)`
- `read(Request $request)`
- `save(Request $request)`
- `create(Request $request)`
- `delete(Request $request)`
- `rename(Request $request)`
- `upload(Request $request)`

**FileExplorerService.php**

- `basePath() → string`
- `resolveExistingPath(string $relativePath) → ?string`
- `safeNewPath(string $relativePath) → array`
- `scanTree(string $targetDir = '') → array`
- `readFile(string $relativePath) → ?array`
- `saveFile(string $relativePath, string $content) → array`
- `createItem(string $relativePath, string $type, string $initialContent = '') → array`
- `deleteItem(string $relativePath) → ?string`
- `renameItem(string $relativePath, string $newName) → ?string`
- `uploadFile($file, string $targetDirectory = '') → array`

## Skenario yang tercantum dalam tes

- isolates identical files and chat histories by owned project
- explains each Git and runner prerequisite

## Navigasi dokumentasi

- [Pemilihan proyek dan kesiapan Agent](03-proyek.md)
- [Editor, tab, dan peninjauan diff](05-editor.md)
