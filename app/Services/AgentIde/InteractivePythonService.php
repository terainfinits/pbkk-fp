<?php

namespace App\Services\AgentIde;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;

class InteractivePythonService
{
    public function __construct(private KernelRunnerService $kernels, private SandboxService $sandbox) {}

    /** @return array{runId: string, executable: string} */
    public function start(string $code, string $cwd, string $owner): array
    {
        $this->sandbox->requireAvailable();
        $runtime = $this->kernels->pythonRuntime();
        abort_unless($runtime !== null, 422, 'Python 3 is not installed or is not on the server PATH.');

        $root = config('ide.runs_root');
        File::ensureDirectoryExists($root, 0700);
        // Workers have a five-minute lifetime. Retain finished output for one hour.
        foreach (File::directories($root) as $old) {
            if (filemtime($old) < time() - 3600) {
                File::deleteDirectory($old);
            }
        }

        $id = bin2hex(random_bytes(24));
        $directory = $root.'/'.$id;
        File::makeDirectory($directory, 0700);
        File::put($directory.'/owner', hash('sha256', $owner));
        File::put($directory.'/code.py', $code);
        File::put($directory.'/input.jsonl', '');
        $command = $this->sandbox->command([...$this->sandbox->runtime('python'), '-u', '/run/code'], '', $directory.'/code.py');
        File::put($directory.'/command.json', json_encode($command, JSON_THROW_ON_ERROR));
        File::put($directory.'/cleanup.json', json_encode($this->sandbox->cleanupCommand($command), JSON_THROW_ON_ERROR));
        File::put($directory.'/limits.json', json_encode(['seconds' => config()->integer('ide.execution_seconds'), 'interactive' => true], JSON_THROW_ON_ERROR));
        File::put($directory.'/state.json', json_encode([
            'running' => true, 'stdout' => '', 'stderr' => '',
            'exitCode' => null, 'executionTimeMs' => 0,
        ], JSON_THROW_ON_ERROR));

        // This launcher exits immediately; its detached worker owns the Python pipes.
        $launcher = new Process([
            ...$runtime['command'], base_path('scripts/python_session.py'), $directory, $cwd,
        ]);
        $launcher->setTimeout(10);
        try {
            $launcher->mustRun();
        } catch (\Throwable $exception) {
            File::deleteDirectory($directory);
            report($exception);
            abort(503, 'Unable to start the Python worker.');
        }

        return ['runId' => $id, 'executable' => $runtime['executable']];
    }

    /** @return array<string, mixed> */
    public function status(string $id, string $owner): array
    {
        $directory = $this->directory($id, $owner);
        $state = json_decode(File::get($directory.'/state.json'), true, 512, JSON_THROW_ON_ERROR);
        // A killed worker must not leave the browser polling forever.
        if ($state['running'] && filemtime($directory.'/state.json') < time() - 15) {
            $state['running'] = false;
            $state['exitCode'] = -1;
            $state['stderr'] .= "\nPython worker stopped responding.\n";
            File::put($directory.'/stop', '1');
        }

        return $state;
    }

    public function input(string $id, string $owner, string $input): void
    {
        $directory = $this->directory($id, $owner);
        abort_unless($this->status($id, $owner)['running'], 409, 'This Python run has finished.');
        file_put_contents($directory.'/input.jsonl', json_encode($input."\n", JSON_THROW_ON_ERROR)."\n", FILE_APPEND | LOCK_EX);
    }

    public function stop(string $id, string $owner): void
    {
        File::put($this->directory($id, $owner).'/stop', '1');
    }

    private function directory(string $id, string $owner): string
    {
        abort_unless(preg_match('/\A[a-f0-9]{48}\z/', $id) === 1, 404);
        $directory = config('ide.runs_root').'/'.$id;
        abort_unless(is_file($directory.'/owner'), 404);
        abort_unless(hash_equals(File::get($directory.'/owner'), hash('sha256', $owner)), 404);

        return $directory;
    }

    public function stopForSession(Request $request): void
    {
        if ($id = $request->session()->get('python_run')) {
            try {
                $this->stop($id, $this->owner($request));
            } catch (NotFoundHttpException) {
            }
        }
        $request->session()->forget(['python_run', 'python_owner']);
    }

    public function owner(Request $request): string
    {
        abort_unless($request->user() !== null, 401);
        if (! $request->session()->has('python_owner')) {
            $request->session()->put('python_owner', bin2hex(random_bytes(32)));
        }

        return $request->user()->id.':'.$request->session()->get('python_owner');
    }
}
