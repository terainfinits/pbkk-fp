# PBKK-FP

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

## Routes list

- `/` home page <br>
- `/agent/{tema?}` agent page <br>
- `/mahasiswa/{nrp 10 digit}` student profile page <br>
- `/hitung-ipk/{ip1?}/{ip2?}` ipk calculator <br>
- `/dashboard/mahasiswa/{nrp 10 digit}` student profile page but using dashboard prefix <br>
- `/dashboard/` home page but using dashboard prefix <br>

## Interactive Python

Open a `.py` file in the IDE and press **F5** (or **Run Code**). Output and
`input()` prompts appear in the **Kernel** tab. Type each answer in **Program
input** and press Enter; an empty answer is supported. Use **Stop**, or Ctrl+C
while focused in Program input, to end the run. Try `folder-generated/input_demo.py`.

The server needs Python 3 (`python3` or `python` on its PATH) and permission to
launch background processes and write to `storage/app/python_runs`. A small
Python supervisor keeps stdin open between HTTP requests; no extra Python
packages or queue worker are needed. Runs are limited to five minutes and 1 MB
of captured output. Finished run files are removed on a later start once they
are over an hour old. A new run in the same browser session stops the previous
one. The shell Terminal tab still executes separate, non-interactive commands.

These processes run on the application host; this feature does not implement
the per-user execution sandbox described in `PLAN.md`.

version pre-release

- `v0.0.0` base project Agung pbkk-tugas-2
- `v0.1.0` connect to gemini
- `v0.2.0` connect to ollama cloud
- `v0.2.1` modularize file controller
- `v0.3.0` add terminal feature, bug exist in terminal
- `v0.3.1` fix terminal bug
- `v0.4.0` change workspace directory, automatically write file to text editor after prompt and UI accept/reject changes and view diff; bug input terminal when running python with function input()
