"""Detached supervisor for a single interactive Python run (no extra packages)."""

import codecs
import json
import os
from pathlib import Path
import queue
import signal
import subprocess
import sys
import threading
import time
from process_control import ProcessTree

MAX_SECONDS = 300
MAX_OUTPUT = 1_000_000


def supervise(directory, cwd):
    started = time.monotonic()
    state = dict(running=True, stdout="", stderr="", exitCode=None, executionTimeMs=0)
    process = None
    tree = None
    cleanup = []
    cleaned_up = False

    def publish():
        state["executionTimeMs"] = round((time.monotonic() - started) * 1000)
        temporary = directory / "state.tmp"
        temporary.write_text(json.dumps(state), encoding="utf-8")
        for attempt in range(5):
            try:
                os.replace(temporary, directory / "state.json")
                break
            except PermissionError:
                if attempt == 4:
                    raise
                time.sleep(0.01)

    def terminate():
        nonlocal cleaned_up
        if cleanup and not cleaned_up:
            cleaned_up = True
            try:
                subprocess.run(cleanup, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, timeout=5)
            except (OSError, subprocess.TimeoutExpired):
                pass
        if process is None:
            return
        if tree is not None:
            tree.stop()
        elif process.poll() is None:
            if os.name == 'nt':
                subprocess.run(['taskkill', '/PID', str(process.pid), '/T', '/F'], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, timeout=5)
            else:
                os.killpg(process.pid, signal.SIGKILL)

    try:
        # Preserve the environment needed by native Windows runtimes and Docker.
        # Native execution is restricted to trusted, non-production use by PHP.
        environment = dict(os.environ, PYTHONIOENCODING="utf-8")
        environment['PYTHONUTF8'] = '1'
        environment['PYTHONPATH'] = cwd + (os.pathsep + environment['PYTHONPATH'] if environment.get('PYTHONPATH') else '')
        settings = json.loads((directory / 'limits.json').read_text(encoding='utf-8')) if (directory / 'limits.json').exists() else {}
        seconds = settings.get('seconds', MAX_SECONDS)
        interactive = settings.get('interactive', True)
        if (directory / "cleanup.json").exists():
            cleanup = json.loads((directory / "cleanup.json").read_text(encoding="utf-8"))
        command = json.loads((directory / "command.json").read_text(encoding="utf-8"))
        gate = directory / 'process-ready'
        if os.name == 'nt':
            # The trusted wrapper waits until it belongs to our Job Object before
            # starting user code, so even short-lived launchers cannot escape cleanup.
            command = [sys.executable, str(Path(__file__).with_name('process_control.py')),
                       '--gated', str(gate), json.dumps(command, ensure_ascii=False)]
        process = subprocess.Popen(
            command,
            cwd=cwd, env=environment, stdin=subprocess.PIPE if interactive else subprocess.DEVNULL,
            stdout=subprocess.PIPE, stderr=subprocess.PIPE, start_new_session=True,
            creationflags=subprocess.CREATE_NEW_PROCESS_GROUP if os.name == 'nt' else 0,
        )
        tree = ProcessTree(process)
        if os.name == 'nt':
            gate.touch()
        output = queue.Queue(maxsize=256)
        inputs = queue.Queue(maxsize=128)

        def read_output(pipe, name):
            decoder = codecs.getincrementaldecoder("utf-8")(errors="replace")
            try:
                while chunk := pipe.read1(4096):
                    output.put((name, decoder.decode(chunk)))
                output.put((name, decoder.decode(b"", final=True)))
            finally:
                pipe.close()

        def write_input():
            try:
                while True:
                    text = inputs.get()
                    if text is None:
                        break
                    process.stdin.write(text.encode("utf-8"))
                    process.stdin.flush()
            except (BrokenPipeError, OSError):
                pass

        readers = [threading.Thread(target=read_output, args=(pipe, name), daemon=True)
                   for pipe, name in [(process.stdout, "stdout"), (process.stderr, "stderr")]]
        for reader in readers:
            reader.start()
        if interactive:
            threading.Thread(target=write_input, daemon=True).start()
        pending = ""
        with (directory / "input.jsonl").open(encoding="utf-8") as mailbox:
            while True:
                reason = None
                if (directory / "stop").exists():
                    reason = "Stopped."
                elif time.monotonic() - started > seconds:
                    reason = "Execution timed out after 5 minutes." if seconds == 300 else f"Execution timed out after {seconds} seconds."

                # Limit each drain so a noisy program cannot starve Stop/timeout checks.
                for _ in range(256):
                    try:
                        name, chunk = output.get_nowait()
                    except queue.Empty:
                        break
                    remaining = MAX_OUTPUT - len(state["stdout"]) - len(state["stderr"])
                    state[name] += chunk[:max(0, remaining)]
                    if len(chunk) > remaining:
                        reason = "Output limit reached (1 MB)."
                        break

                if reason:
                    state["stderr"] += "\n" + reason + "\n"
                    terminate()
                    process.wait(timeout=5)
                    state.update(running=False, exitCode=process.returncode)
                    break

                if process.poll() is not None:
                    # Kill any descendants retaining the pipes before finishing output.
                    terminate()
                    if all(not reader.is_alive() for reader in readers) and output.empty():
                        state.update(running=False, exitCode=process.returncode)
                        break

                pending += mailbox.read(65536)
                while "\n" in pending and not inputs.full():
                    line, pending = pending.split("\n", 1)
                    inputs.put_nowait(json.loads(line))
                publish()
                time.sleep(0.05)
    except Exception as error:
        terminate()
        state.update(running=False, exitCode=-1)
        state["stderr"] += f"\nPython runner failed: {error}\n"
    finally:
        terminate()
        publish()


if __name__ == "__main__":
    if sys.argv[1] == "--worker":
        supervise(Path(sys.argv[2]), sys.argv[3])
    else:
        subprocess.Popen(
            [sys.executable, __file__, "--worker", *sys.argv[1:]],
            stdin=subprocess.DEVNULL, stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL, close_fds=True, start_new_session=True,
            creationflags=(subprocess.DETACHED_PROCESS | subprocess.CREATE_NEW_PROCESS_GROUP) if os.name == 'nt' else 0,
        )
