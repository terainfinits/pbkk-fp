<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Illuminate\Support\Facades\Schedule::call(function (): void {
    $runs = Illuminate\Support\Facades\DB::table('agent_runs')
        ->whereIn('status', ['running', 'cancelling'])->where('updated_at', '<', now()->subMinutes(11))->get();
    foreach ($runs as $run) {
        $db = Illuminate\Support\Facades\DB::table('agent_runs')->where('id', $run->id)->where('status', $run->status);
        if (!$db->update(['active' => null, 'status' => $run->status === 'cancelling' ? 'cancelled' : 'failed'])) {
            continue;
        }
        Illuminate\Support\Facades\DB::table('run_events')->insert(['agent_run_id' => $run->id,
            'kind' => 'error', 'message' => 'Worker terputus atau batas waktu tercapai.']);
        $space = Illuminate\Support\Facades\DB::table('workspaces')->find($run->workspace_id);
        $name = 'laravel-harness-'.substr(hash('sha256', base_path()), 0, 12).'-'.$run->id;
        try {
            (new Symfony\Component\Process\Process(['docker', 'rm', '-f', $name]))->setTimeout(5)->run();
        } finally {
            $repo = app(App\Services\Harness\Repository::class);
            $repo->discard($space, $run->id);
            Illuminate\Support\Facades\File::deleteDirectory($repo->tree($run->id).'-snapshot');
        }
    }
})->name('harness-recover')->everyMinute()->withoutOverlapping();
