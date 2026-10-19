# Windows-First Runtime Compatibility Plan

Status: implementation complete; Linux checks and browser smoke test passed.
Native Windows verification is pending a real Windows run or the new Windows CI job.

## Goal

Make Magentic work through its normal setup on Windows, with Linux compatibility.
Local development should require PHP, Composer, Node.js/npm, and Python 3, without
requiring Docker, WSL, Bubblewrap, or additional Python packages.

Keep login, registration, private user workspaces, editing, AI chat, diff review,
and the existing F5 interactive Python experience.

## Original blockers addressed

- Windows currently selects Docker and requires a separately built runtime image.
- The alternative Bubblewrap runner is Linux-only.
- The filesystem helper uses POSIX directory descriptors and `O_NOFOLLOW`.
- Several execution paths assume `/usr/bin/...` and a Unix shell.
- Process startup, background execution, and stopping descendants differ between Windows and Linux.

Python itself supports both platforms. These blockers come from the surrounding
runner and file-handling implementation.

## Proposed behavior

- Use native execution by default for local development on Windows and Linux.
- Keep Docker as an optional isolated runner on either platform.
- Keep Bubblewrap as an optional Linux runner if retaining it remains simple.
- Require an available isolated runner for public production deployments. Do not silently fall back to native execution there.
- Native execution is for local development and trusted users. Separate workspace directories control application file access; they do not restrict what an executed program can access on the host.
- Keep local development bound to localhost by default and document this trust boundary in setup instructions.

## Implementation plan

### 1. Make execution selection explicit

- Introduce one runner selection setting with `native`, `docker`, and optional `bubblewrap` values.
- Default local development to `native`; require isolation in production.
- Keep operating-system differences inside small runtime/process helpers rather than branching throughout controllers and Vue components.
- Preserve existing API URLs and response shapes so the frontend needs little or no change.
- Show a clear error when a selected executable or runner is unavailable.

### 2. Detect runtimes without fixed installation paths

- On Windows, check available Python 3 commands such as `python`, `py -3`, and `python3`.
- On Linux, check `python3` and then `python`.
- Verify that the selected interpreter is Python 3 and retain its complete argument list. Detection and execution must use the same selection.
- Resolve PHP and Node from their configured paths or PATH rather than `/usr/bin/...`.
- Pass executable paths and script arguments as arrays so spaces and Unicode in Windows installation paths work correctly.
- Keep unsupported languages explicit. C support remains outside this change.

### 3. Keep F5 interactive Python working

- Preserve one running process across multiple `input()` calls, live output polling, blank answers, whitespace, and Unicode input.
- Keep Python output unbuffered so prompts appear before the program finishes.
- Preserve the host environment needed to start trusted runtime tools on each platform, including Windows `SystemRoot` and PATH.
- Use platform-appropriate background-process flags.
- On Windows, terminate the owned process tree using a suitable built-in mechanism; on Linux, terminate its process group.
- Only use process identifiers recorded by the server for that owned run. Do not accept arbitrary process IDs from API callers.
- Keep Stop, logout cleanup, wall-time limits, output limits, and run ownership checks.
- Keep Docker container cleanup when the optional Docker runner is selected.
- Document that native mode does not provide the memory, CPU, process, filesystem, or network isolation supplied by an isolated runner.

### 4. Replace the mandatory Linux filesystem helper

- Use portable Laravel/PHP file operations for workspace listing, reading, writing, creation, and deletion.
- Remove the POSIX Python helper from the default file API path. File editing must not require Docker or a Python subprocess.
- Continue deriving workspace ownership from the authenticated user.
- Keep canonical path validation, traversal rejection, root-deletion protection, and checks against symlinks and Windows junction/reparse points.
- Handle Windows drive paths, UNC paths, separators, reserved filenames, spaces, and Unicode correctly. API paths remain relative to the current user's workspace.
- Use appropriate file locking and write handling to reduce concurrent edit problems.
- Do not describe portable path checks as a sandbox against arbitrary native programs. Public untrusted execution must remain isolated.
- Preserve existing workspaces, database contents, and the designated legacy-file owner.

### 5. Select the correct shell

- Use available PowerShell on Windows and Bash on Linux.
- Report the actual server shell in the terminal instead of inferring it from the browser's operating system.
- Preserve relative working-directory navigation and existing command responses.
- Keep shell commands separate from Python program input: answers to `input()` belong to the running Python process.

### 6. Remove obsolete mandatory dependencies carefully

- Remove Linux-only paths, checks, and helper calls from the native execution path.
- Retain Docker/Bubblewrap code only behind explicitly selected isolation drivers.
- Delete code and dependencies only after their remaining consumers are checked.
- Update `.env.example` and README instructions for normal Windows setup, Linux setup, and optional isolated deployment.
- Avoid unrelated UI changes or authentication rewrites.

### 7. Verify on both platforms

- Add Windows and Linux CI coverage for portable file operations and native process execution.
- Test interpreter selection, including Windows' `py -3` argument handling and executable paths containing spaces.
- Test F5 with several prompts, empty input, Unicode, tracebacks, Stop, timeout, and logout during a run.
- Test PowerShell/Bash selection, working-directory navigation, and missing executable errors.
- Retest authentication, file ownership, traversal attempts, links/junctions, and preservation of existing files.
- Test that production rejects native execution and unavailable isolation does not trigger an unsafe fallback.
- Keep optional isolated-runner integration tests separate from tests needed for default local setup.
- Run frontend lint, type checks, build, and backend checks. Record any pre-existing failures separately.
- Perform a real Windows smoke test before claiming native Windows support is verified; command-generation tests alone are insufficient.

## Completion criteria

- Normal Windows setup opens the authenticated IDE and supports file editing without Docker or WSL.
- F5 runs Python, accepts successive answers, streams output, and stops the complete owned process tree.
- The same workflow works on Linux with available native runtimes.
- Authentication and user workspace ownership remain enforced.
- Optional isolation remains available for deployments that execute untrusted code.
- Existing database records and code files remain intact.

## Verification record

- [x] Native local execution is the default; file editing requires neither Python nor Docker.
- [x] Runtime selection, Windows launcher arguments, PowerShell selection, spaces/Unicode, ownership, links/junction validation, and production rejection are covered by tests.
- [x] Full CI checks pass locally: frontend lint/types, PHP formatting, PHPStan (zero errors), and all 32 backend tests, including the optional Bubblewrap integration test when run with namespace access.
- [x] All three process-supervision tests pass, including timeout cleanup of descendant processes.
- [x] Frontend production build passes.
- [x] Linux browser smoke test passes: sign in, open a private file, press F5, answer `input()`, receive output and exit code zero.
- [x] Windows/Linux CI matrix is configured with Python 3.13 and PHP 8.4.
- [ ] Run the Windows CI job and a native Windows F5/input/Stop smoke test. This Linux workspace cannot confirm native Windows behavior by itself.

## Short summary

### What will be added or changed

- Native Windows execution by default for local development, with Linux compatibility.
- Portable Python/PHP/Node detection and platform-aware process management.
- Portable file operations and the correct server shell.
- Windows and Linux tests plus simpler setup instructions.

### What will be removed or made optional

- Mandatory Docker setup for local Windows use.
- Mandatory Linux filesystem APIs and fixed `/usr/bin/...` paths.
- Bubblewrap and Docker requirements in the default local workflow; isolation remains required for public untrusted execution.

### What will be preserved

- Login, registration, private workspaces, AI editing, diff review, and interactive Python input.
- Existing user accounts, generated files, and legacy-file ownership.
