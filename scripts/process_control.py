"""Own and terminate a process tree using POSIX groups or a Windows Job Object."""

import os
import signal
import subprocess


class ProcessTree:
    def __init__(self, process):
        self.process = process
        self.job = None
        if os.name == 'nt':
            self._attach_windows_job()

    def _attach_windows_job(self):
        import ctypes
        from ctypes import wintypes

        class BasicLimits(ctypes.Structure):
            _fields_ = [('process_time', ctypes.c_int64), ('job_time', ctypes.c_int64),
                        ('flags', wintypes.DWORD), ('min_working', ctypes.c_size_t),
                        ('max_working', ctypes.c_size_t), ('active_processes', wintypes.DWORD),
                        ('affinity', ctypes.c_size_t), ('priority', wintypes.DWORD),
                        ('scheduling', wintypes.DWORD)]

        class IOCounters(ctypes.Structure):
            _fields_ = [(name, ctypes.c_uint64) for name in ('read_ops', 'write_ops', 'other_ops',
                                                          'read_bytes', 'write_bytes', 'other_bytes')]

        class ExtendedLimits(ctypes.Structure):
            _fields_ = [('basic', BasicLimits), ('io', IOCounters),
                        ('process_memory', ctypes.c_size_t), ('job_memory', ctypes.c_size_t),
                        ('peak_process_memory', ctypes.c_size_t), ('peak_job_memory', ctypes.c_size_t)]

        kernel = ctypes.WinDLL('kernel32', use_last_error=True)
        kernel.CreateJobObjectW.argtypes = [ctypes.c_void_p, wintypes.LPCWSTR]
        kernel.CreateJobObjectW.restype = wintypes.HANDLE
        kernel.SetInformationJobObject.argtypes = [wintypes.HANDLE, ctypes.c_int, ctypes.c_void_p, wintypes.DWORD]
        kernel.SetInformationJobObject.restype = wintypes.BOOL
        kernel.AssignProcessToJobObject.argtypes = [wintypes.HANDLE, wintypes.HANDLE]
        kernel.AssignProcessToJobObject.restype = wintypes.BOOL
        kernel.OpenProcess.argtypes = [wintypes.DWORD, wintypes.BOOL, wintypes.DWORD]
        kernel.OpenProcess.restype = wintypes.HANDLE
        kernel.CloseHandle.argtypes = [wintypes.HANDLE]
        kernel.CloseHandle.restype = wintypes.BOOL
        self.kernel = kernel
        job = kernel.CreateJobObjectW(None, None)
        if not job:
            raise ctypes.WinError(ctypes.get_last_error())
        try:
            limits = ExtendedLimits()
            limits.basic.flags = 0x2000  # JOB_OBJECT_LIMIT_KILL_ON_JOB_CLOSE
            if not kernel.SetInformationJobObject(job, 9, ctypes.byref(limits), ctypes.sizeof(limits)):
                raise ctypes.WinError(ctypes.get_last_error())
            handle = kernel.OpenProcess(0x0100 | 0x0001, False, self.process.pid)
            if not handle:
                if self.process.poll() is not None:
                    return
                raise ctypes.WinError(ctypes.get_last_error())
            try:
                if not kernel.AssignProcessToJobObject(job, handle):
                    if self.process.poll() is not None:
                        return
                    raise ctypes.WinError(ctypes.get_last_error())
            finally:
                kernel.CloseHandle(handle)
            self.job = job
            job = None
        finally:
            if job:
                kernel.CloseHandle(job)

    def stop(self):
        if os.name == 'nt':
            if self.job:
                self.kernel.CloseHandle(self.job)
                self.job = None
            elif self.process.poll() is None:
                subprocess.run(['taskkill', '/PID', str(self.process.pid), '/T', '/F'],
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, timeout=5)
        else:
            try:
                os.killpg(self.process.pid, signal.SIGKILL)
            except ProcessLookupError:
                pass


if __name__ == '__main__':
    import json
    from pathlib import Path
    import sys
    import time

    if sys.argv[1] != '--gated':
        raise SystemExit('Unknown process-control mode')
    gate = Path(sys.argv[2])
    deadline = time.monotonic() + 10
    while not gate.exists():
        if time.monotonic() > deadline:
            raise SystemExit('The process tree could not be initialized')
        time.sleep(0.01)
    child = subprocess.Popen(json.loads(sys.argv[3]), stdin=sys.stdin.buffer,
                             stdout=sys.stdout.buffer, stderr=sys.stderr.buffer)
    raise SystemExit(child.wait())
