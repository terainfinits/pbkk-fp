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

- `/login` and `/register`: account access.
- `/`: authenticated Magentic IDE; `/ide` redirects here.
- `/ide/api/*`: authenticated file, chat, terminal, and execution endpoints.

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

## Windows-first local setup

With `APP_ENV=local`, Magentic uses native execution on Windows and Linux by
default. Docker, WSL, Bubblewrap, and extra Python packages are not required for
local use. Install PHP 8.4.1+ (including SQLite/PDO; required by the locked development tools), Composer, Node.js/npm, and
Python 3. The normal setup commands above are sufficient. In PowerShell, the
`.env` copy command can also be written as `Copy-Item .env.example .env`.

Python is detected as `python`, `py -3`, or `python3` on Windows, and `python3`
or `python` on Linux. The selected interpreter and its arguments are used for
both detection and execution. PHP and Node are also resolved from their actual
installations. Optional `.env` overrides accept executable paths with spaces:

```dotenv
# IDE_PYTHON_BINARY="C:/Program Files/Python313/python.exe"
# IDE_PHP_BINARY="C:/php/php.exe"
# IDE_NODE_BINARY="C:/Program Files/nodejs/node.exe"
# IDE_SHELL_BINARY="C:/Program Files/PowerShell/7/pwsh.exe"
```

After changing `.env`, run `php artisan config:clear` and restart
`composer run dev`. Remove a previous `IDE_SANDBOX_DRIVER=docker` override to use
the local native default. Windows uses available PowerShell; Linux uses Bash.
The terminal reports the server's shell, regardless of the browser's OS.
File editing uses PHP and does not require Python or a running execution driver.
Windows reserved filenames, symlinks, junctions, and traversal are rejected.
Existing accounts, private workspace files, and legacy-file ownership are kept.

Run the development server on localhost (the default for `php artisan serve`).
Native programs run with the server account's permissions and host environment;
private workspace paths are not an operating-system sandbox. Native mode is for
local development and trusted users. It keeps wall-time/output limits and
process-tree cleanup, but does not isolate files, network, memory, or CPU usage.
Synchronous runs stop after 30 seconds; interactive Python stops after five minutes.
Windows uses [Job Objects](https://learn.microsoft.com/en-us/windows/win32/procthread/job-objects) to clean up child processes; Linux uses process groups.

## Optional isolated deployment

Native execution is rejected outside `local` and `testing` environments, even
when explicitly selected. Production requires a working isolated runner and
never falls back to host execution. Set `APP_ENV=production` for public hosting.
Choose an isolated driver explicitly when needed:

```dotenv
IDE_SANDBOX_DRIVER=docker
IDE_DOCKER_IMAGE=magentic-runtime:local
```

For Docker, start Docker Desktop in Linux-container mode on Windows (or Docker
Engine on Linux), then build the optional image once:

```powershell
docker build -t magentic-runtime:local -f docker/runtime.Dockerfile .
```

Linux may instead select `IDE_SANDBOX_DRIVER=bubblewrap`, with Bubblewrap,
util-linux, and unprivileged user namespaces available to the server account.
Isolation limits filesystem access, network access, memory, CPU, and process
counts. Production file APIs retain descriptor-based operations for protection
against concurrent symlink changes; Docker is required for those operations on
Windows. Local file APIs remain independent of Docker and Python.

## Validation

```bash
npm run check
npm run types:check
npm run build
composer test
python -B -m unittest discover -s tests/python
```

Use `python3` for the last command if that is your Linux Python executable.
Windows and Linux CI run the native test suite. The Bubblewrap integration test
is optional and skips where that driver is unavailable. Native Windows has not
been smoke-tested in this Linux workspace; the Windows CI job and a real Windows
F5/input/Stop test must pass before treating that platform as fully verified.
See `WINDOWS_FIRST_PLAN.md` for implementation and verification status.

version pre-release

- `v0.0.0` base project Agung pbkk-tugas-2
- `v0.1.0` connect to gemini
- `v0.2.0` connect to ollama cloud
- `v0.2.1` modularize file controller
- `v0.3.0` add terminal feature, bug exist in terminal
- `v0.3.1` fix terminal bug
- `v0.4.0` change workspace directory, automatically write file to text editor after prompt and UI accept/reject changes and view diff; bug input terminal when running python with function input()
