# Loop perencanaan dan coding Agent

Worker meminta satu aksi JSON per giliran dan menyusun konteks lanjut dari hasil aktual executor.

## Input

Task, instruksi sistem, klarifikasi, instruksi repo jika tersedia, history dan daftar pemeriksaan.

## Output

Aksi baca, edit, tes atau pertanyaan. Run menyimpan rencana, bukti, patch dan alasan berhenti.

## Alur kerja

1. Worker memulai checkout dan membatasi loop hingga 20 giliran atau deadline 600 detik.
2. AgentAction memvalidasi bentuk aksi.
3. List, read dan search menyediakan konteks.
4. Plan memuat file, langkah dan kriteria.
5. Sebelum edit pertama worker mengambil baseline tes.
6. Hasil aktual atau tool error masuk ke konteks berikutnya.
7. Tiga aksi invalid berturut-turut menghentikan loop dengan invalid_actions.
8. Ask menyimpan pertanyaan, sedangkan jawaban pengguna mengantrekan kelanjutan.

## Tanggung jawab setiap file

| File | Peran dalam fitur |
|---|---|
| [app/Jobs/RunCodingAgent.php](../../app/Jobs/RunCodingAgent.php) | State machine giliran, batas waktu dan keputusan akhir. |
| [app/Services/Harness/SenopatiAgent.php](../../app/Services/Harness/SenopatiAgent.php) | Mengirim payload chat completions. |
| [app/Services/Harness/AgentAction.php](../../app/Services/Harness/AgentAction.php) | Parser aksi dan validasi argumen. |
| [app/Services/Harness/Tools.php](../../app/Services/Harness/Tools.php) | Implementasi list, read, search, edit dan test. |
| [resources/js/components/agent-ide/HarnessPanel.vue](../../resources/js/components/agent-ide/HarnessPanel.vue) | Prompt serta jawaban klarifikasi. |
| [tests/Feature/HarnessTest.php](../../tests/Feature/HarnessTest.php) | Skenario aksi, kesalahan dan klarifikasi. |
| [tests/Feature/LiveHarnessTest.php](../../tests/Feature/LiveHarnessTest.php) | Pengujian integrasi dengan provider live. |

## Metode publik pada implementasi terkait

**RunCodingAgent.php**

- `handle(Repository $repo, Tools $tools) → void`
- `failed(?Throwable $e) → void`

**SenopatiAgent.php**

- `recordBodies(\Closure $record) → void`
- `next(array $messages, int $timeout) → string`

**AgentAction.php**

- `parse(string $raw, array $checks) → array`

**Tools.php**

- `files(string $dir) → array`
- `read(string $dir, string $path, int $start = 1, int $count = 100) → string`
- `edit(string $dir, string $path, string $old, string $new) → string`
- `search(string $dir, string $text) → string`
- `test(string $dir, object $space, int $id, callable $check) → array`

## Skenario yang tercantum dalam tes

- reviews a two-file change and only applies it to an unchanged owned repository
- keeps a valid action when optional progress is unusable
- distinguishes DNS, connection and timeout failures
- asks Senopati to repair a real repository and checks it in Docker before approval

## Navigasi dokumentasi

- [Memulai tugas dan menjalankan worker otomatis](11-antrean.md)
- [Isolasi perubahan melalui worktree dan snapshot folder](13-snapshot.md)
