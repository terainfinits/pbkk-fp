<?php

namespace App\Services\Harness;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class WorkerLauncher
{
    public function wake(int $run): void
    {
        if (config('queue.harness_mode') === 'managed') {
            return;
        }

        try {
            $this->launch($run);
        } catch (Throwable) {
            DB::table('agent_runs')->where('id', $run)->where('status', 'queued')
                ->update(['stop_reason'=>'worker_launch_failed']);
        }
    }

    public static function lockPath(): string
    {
        $connection = config('queue.connections.database.connection') ?: config('database.default');
        $identity = [$connection, DB::connection($connection)->getDatabaseName(), config('queue.connections.database.table')];
        return storage_path('framework/harness-worker-'.hash('sha256', serialize($identity)).'.lock');
    }

    public function missing(object $run): bool
    {
        // A detached process can fail to bootstrap after the OS accepts the launch.
        return config('queue.harness_mode') === 'local' && $run->status === 'queued'
            && $run->phase !== 'worker_ready' && !$run->stop_reason
            && now()->diffInSeconds($run->updated_at, true) >= 20;
    }

    protected function launch(int $run): void
    {
        $php = (new PhpExecutableFinder)->find(false);
        if (!$php || !is_file($php)) {
            throw new \RuntimeException('PHP CLI tidak tersedia.');
        }
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = ['powershell.exe', '-NoProfile', '-NonInteractive', '-Command',
                "\$ErrorActionPreference='Stop'; Start-Process -WindowStyle Hidden -FilePath \$env:AGENT_PHP -WorkingDirectory \$env:AGENT_ROOT -ArgumentList 'artisan','harness:drain',\$env:AGENT_RUN"];
        } else {
            $cmd = ['/bin/sh', '-c', 'nohup "$AGENT_PHP" artisan harness:drain "$AGENT_RUN" </dev/null >/dev/null 2>&1 &'];
        }
        (new Process($cmd, base_path(), ['AGENT_PHP'=>$php,'AGENT_ROOT'=>base_path(),'AGENT_RUN'=>(string)$run]))->setTimeout(10)->mustRun();
    }
}
