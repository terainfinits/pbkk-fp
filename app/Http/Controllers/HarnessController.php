<?php
// Fitur: Harness tugas coding
// connect with ../../../database/migrations/2026_11_14_000000_create_harness_tables.php, ../../../routes/harness.php, ../../Jobs/RunCodingAgent.php
// connect with ../../Services/Harness/Repository.php, ./Controller.php

namespace App\Http\Controllers;

use App\Jobs\RunCodingAgent;
use App\Services\Harness\Repository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HarnessController extends Controller
{
    public function index(Request $request)
    {
        $spaces = DB::table('workspaces')->where('user_id', $request->user()->id)->get(['id', 'name']);
        foreach ($spaces as $space) {
            $space->latest = DB::table('agent_runs')->where('workspace_id', $space->id)->max('id');
        }
        return response()->json($spaces);
    }

    public function startFolder(Request $request, Repository $repo)
    {
        return $this->start($request, app(\App\Services\AgentIde\Projects::class)->folderWorkspace(), $repo);
    }

    public function start(Request $request, int $workspace, Repository $repo)
    {
        $task = $request->validate(['task' => 'required|string|max:8000'])['task'];
        $space = DB::table('workspaces')->where('user_id', $request->user()->id)->find($workspace);
        abort_unless($space, 404);
        $reason = app(\App\Services\AgentIde\Projects::class)->reason($space->path, $space->image);
        abort_if($reason !== null, 409, $reason ?? '');
        try {
            $base = \App\Services\Harness\FolderWorkspace::matches($space->path) ? 'folder' : $repo->cleanHead($space->path);
        } catch (\RuntimeException $e) {
            abort(409, $e->getMessage());
        }
        try {
            $id = DB::transaction(function () use ($workspace, $task, $base) {
                DB::table('agent_runs')->where('workspace_id', $workspace)
                    ->where('status', 'review')->where('active', true)
                    ->where(fn ($query) => $query->whereNull('patch')->orWhere('patch', ''))
                    ->update(['active' => null]);

                $id = DB::table('agent_runs')->insertGetId(['workspace_id' => $workspace, 'task' => $task,
                    'base' => $base, 'active' => true, 'status' => 'queued', 'created_at' => now(), 'updated_at' => now()]);
                DB::table('run_events')->insert(['agent_run_id' => $id, 'kind' => 'queued', 'message' => 'Menunggu worker',
                    'time_ms' => (int) (microtime(true)*1000), 'activity' => 'queue']);
                $this->enqueue($id);
                return $id;
            });
        } catch (UniqueConstraintViolationException) {
            abort(409, 'Selesaikan run aktif terlebih dahulu.');
        }
        return response()->json(['id' => $id, 'workspace' => $workspace], 202);
    }

    private function enqueue(int $id): void
    {
        $connection = config('queue.harness_mode') === 'local' ? 'database' : config('queue.default');
        RunCodingAgent::dispatch($id)->onConnection($connection)->onQueue('harness')->afterCommit();
        DB::afterCommit(fn () => app(\App\Services\Harness\WorkerLauncher::class)->wake($id));
    }

    public function history(Request $request, int $workspace)
    {
        abort_unless(DB::table('workspaces')->where('user_id', $request->user()->id)->where('id', $workspace)->exists(), 404);
        return response()->json(DB::table('agent_runs')->where('workspace_id', $workspace)->orderByDesc('id')
            ->simplePaginate(20, ['id', 'task', 'status']));
    }

    private function owned(Request $request, int $id): object
    {
        $run = DB::table('agent_runs')->join('workspaces', 'workspaces.id', '=', 'agent_runs.workspace_id')
            ->where('workspaces.user_id', $request->user()->id)->where('agent_runs.id', $id)
            ->select('agent_runs.*', 'workspaces.path')->first();
        abort_unless($run, 404);
        return $run;
    }

    public function body(Request $request, int $run, int $event)
    {
        $this->owned($request, $run);
        $row = DB::table('run_events')->where('agent_run_id', $run)
            ->whereIn('kind', ['raw_request', 'raw_response'])->find($event);
        abort_unless($row && preg_match('/^provider-[a-f0-9]{12}$/D', $row->activity), 404);
        $path = storage_path('app/private/harness-transcripts/'.$run.'/'.$row->activity.'-'.substr($row->kind, 4).'.txt');
        abort_unless(is_file($path), 404);
        return response()->file($path, ['Content-Type'=>'text/plain; charset=UTF-8',
            'X-Content-Type-Options'=>'nosniff', 'Cache-Control'=>'private, no-store']);
    }

    public function show(Request $request, int $run)
    {
        $row = $this->owned($request, $run);
        if (app(\App\Services\Harness\WorkerLauncher::class)->missing($row)) {
            $row->stop_reason = 'worker_launch_failed';
        }
        return response()->json(['id' => $run, 'status' => $row->status, 'task' => $row->task, 'patch' => $row->patch,
            'plan' => json_decode($row->plan ?? 'null'), 'checks' => json_decode($row->checks ?? '{}'),
            'evidence' => json_decode($row->evidence ?? 'null'), 'revision' => $row->revision,
            'question' => $row->question, 'phase' => $row->phase, 'stop_reason' => $row->stop_reason,
            'changes' => json_decode($row->changes ?? '[]'),
            'events' => DB::table('run_events')->where('agent_run_id', $run)->where('id', '>', $request->integer('after'))
                ->orderBy('id')->get(['id', 'kind', 'message', 'exit_code', 'time_ms', 'turn', 'activity', 'duration_ms'])]);
    }

    public function action(Request $request, int $run, string $action, Repository $repo)
    {
        return DB::transaction(function () use ($request, $run, $action, $repo) {
            $row = $this->owned($request, $run);
            $query = DB::table('agent_runs')->where('id', $run);
            if ($action === 'retry-worker') {
                abort_unless(config('queue.harness_mode') === 'local' && $row->status === 'queued'
                    && ($row->stop_reason === 'worker_launch_failed' || app(\App\Services\Harness\WorkerLauncher::class)->missing($row)), 409);
                $query->where('status','queued')->update(['stop_reason'=>null, 'phase'=>null, 'updated_at'=>now()]);
                DB::afterCommit(fn () => app(\App\Services\Harness\WorkerLauncher::class)->wake($run));
                return response()->noContent();
            }
            if ($action === 'cancel') {
                if ($query->where('status', 'queued')->update(['status' => 'cancelled', 'stop_reason' => 'cancelled', 'active' => null])) {
                    return response()->noContent();
                }
                abort_unless(DB::table('agent_runs')->where('id', $run)->where('status', 'running')->update(['status' => 'cancelling']), 409);
                return response()->noContent();
            }
            if ($action === 'answer') {
                $answer = $request->validate(['answer' => 'required|string|max:4000'])['answer'];
                abort_unless($query->where('status', 'awaiting_input')->update([
                    'clarifications' => $row->clarifications."\n".$row->question."\n".$answer,
                    'question' => null, 'stop_reason' => null, 'status' => 'queued', 'updated_at' => now(),
                ]), 409);
                DB::table('run_events')->insert(['agent_run_id' => $run, 'kind' => 'queued', 'message' => 'Menunggu worker',
                    'time_ms' => (int) (microtime(true)*1000), 'activity' => 'queue']);
                $this->enqueue($run);
                return response()->noContent();
            }
            $allowed = $action === 'apply' ? ['verified'] : ['verified', 'review', 'conflict', 'awaiting_input'];
            abort_unless($query->whereIn('status', $allowed)->update(['status' => 'deciding']), 409);
            if ($action === 'apply') {
                try {
                    if ($row->revision !== hash('sha256', (string) $row->patch)) {
                        throw new \RuntimeException('Patch berubah.');
                    }
                    if ($row->base === 'folder') {
                        app(\App\Services\Harness\FolderWorkspace::class)->apply($repo, $row);
                    } elseif ($repo->cleanHead($row->path) !== $row->base) {
                        throw new \RuntimeException('HEAD berubah.');
                    }
                    if ($row->base !== 'folder' && $row->patch) {
                        $repo->git($row->path, ['apply', '--whitespace=nowarn', '-'], $row->patch);
                    }
                } catch (\RuntimeException $e) {
                    DB::table('agent_runs')->where('id', $run)->update(['status' => 'conflict']);
                    return response()->json(['message' => $row->base === 'folder' ? $e->getMessage() : 'Workspace berubah atau patch tidak dapat diterapkan.'], 409);
                }
            }
            DB::table('agent_runs')->where('id', $run)->update(['status' => $action === 'apply' ? 'applied' : 'rejected', 'active' => null]);
            return response()->noContent();
        });
    }
}
