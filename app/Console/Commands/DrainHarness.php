<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class DrainHarness extends Command
{
    protected $signature = 'harness:drain {run?}';
    protected $description = 'Drain the local harness queue with a single worker';

    public function handle(): int
    {
        $lock = fopen(\App\Services\Harness\WorkerLauncher::lockPath(), 'c');
        if (!$lock) {
            return self::FAILURE;
        }
        try {
            if ($this->argument('run')) {
                \Illuminate\Support\Facades\DB::table('agent_runs')->where('id', $this->argument('run'))
                    ->where('status', 'queued')->update(['phase'=>'worker_ready','stop_reason'=>null]);
            }
            do {
                if (!flock($lock, LOCK_EX | LOCK_NB)) {
                    return self::SUCCESS;
                }
                try {
                    $code = $this->call('queue:work', ['connection'=>'database','--queue'=>'harness',
                        '--stop-when-empty'=>true,'--tries'=>1,'--timeout'=>620,'--sleep'=>0]);
                } finally {
                    flock($lock, LOCK_UN);
                }
                if ($code !== self::SUCCESS) {
                    return $code;
                }
                // A concurrent wake may have observed our lock just before worker exit.
            } while (Queue::connection('database')->pendingSize('harness') > 0);
            return self::SUCCESS;
        } finally {
            fclose($lock);
        }
    }
}
