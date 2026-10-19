"""Run with: python3 -B -m unittest discover -s tests/python."""

import importlib.util
import json
from pathlib import Path
import tempfile
import sys
import unittest
from unittest.mock import patch

spec = importlib.util.spec_from_file_location(
    "python_session", Path(__file__).resolve().parents[2] / "scripts/python_session.py"
)
worker = importlib.util.module_from_spec(spec)
spec.loader.exec_module(worker)


class PythonSessionTest(unittest.TestCase):
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


if __name__ == "__main__":
    unittest.main()
