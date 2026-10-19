"""Run with: python3 -B -m unittest discover -s tests/python."""

import importlib.util
import json
from pathlib import Path
import tempfile
import sys
import unittest
import time
from unittest.mock import patch

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'scripts'))

spec = importlib.util.spec_from_file_location(
    "python_session", Path(__file__).resolve().parents[2] / "scripts/python_session.py"
)
worker = importlib.util.module_from_spec(spec)
spec.loader.exec_module(worker)


class PythonSessionTest(unittest.TestCase):
    def test_timeout_also_stops_native_child_processes(self):
        with tempfile.TemporaryDirectory(prefix='magentic space ') as temporary:
            directory = Path(temporary)
            marker = directory / 'child-survived'
            child = 'import time,sys; from pathlib import Path; time.sleep(2); Path(sys.argv[1]).write_text("alive")'
            code = f'import subprocess,sys\nsubprocess.Popen([sys.executable, "-c", {child!r}, {str(marker)!r}])\nprint("child started", flush=True)\ninput()'
            (directory / 'code.py').write_text(code, encoding='utf-8')
            (directory / 'input.jsonl').write_text('', encoding='utf-8')
            (directory / 'command.json').write_text(json.dumps([sys.executable, '-u', str(directory / 'code.py')]), encoding='utf-8')
            with patch.object(worker, 'MAX_SECONDS', 1):
                worker.supervise(directory, temporary)
            state = json.loads((directory / 'state.json').read_text(encoding='utf-8'))
            self.assertIn('child started', state['stdout'])
            time.sleep(1.3)
            self.assertFalse(marker.exists(), 'A descendant survived process-tree cleanup')

    def test_waiting_for_input_times_out_without_a_polling_browser(self):
        with tempfile.TemporaryDirectory() as temporary:
            directory = Path(temporary)
            (directory / "code.py").write_text("input('Answer: ')", encoding="utf-8")
            (directory / "input.jsonl").write_text("", encoding="utf-8")
            (directory / "command.json").write_text(json.dumps([sys.executable, '-u', str(directory / 'code.py')]), encoding='utf-8')
            with patch.object(worker, "MAX_SECONDS", 0.2):
                worker.supervise(directory, temporary)
            state = json.loads((directory / "state.json").read_text(encoding="utf-8"))
            self.assertFalse(state["running"])
            self.assertNotEqual(state["exitCode"], 0)
            self.assertIn("Answer: ", state["stdout"])
            self.assertIn("Execution timed out", state["stderr"])

    def test_stopping_a_container_run_executes_its_cleanup_command(self):
        with tempfile.TemporaryDirectory() as temporary:
            directory = Path(temporary)
            (directory / 'code.py').write_text("input('Answer: ')", encoding='utf-8')
            (directory / 'input.jsonl').write_text('', encoding='utf-8')
            (directory / 'command.json').write_text(json.dumps([sys.executable, '-u', str(directory / 'code.py')]), encoding='utf-8')
            cleanup = [sys.executable, '-c', 'from pathlib import Path; import sys; Path(sys.argv[1]).write_text("removed")', str(directory / 'container-removed')]
            (directory / 'cleanup.json').write_text(json.dumps(cleanup), encoding='utf-8')
            with patch.object(worker, 'MAX_SECONDS', 0.2):
                worker.supervise(directory, temporary)
            self.assertEqual((directory / 'container-removed').read_text(), 'removed')


if __name__ == "__main__":
    unittest.main()
