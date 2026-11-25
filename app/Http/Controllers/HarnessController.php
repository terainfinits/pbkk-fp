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

    public function start(Request $request, int $workspace, Repository $repo)
    {
        $task = $request->validate(['task' => 'required|string|max:8000'])['task'];
        $space = DB::table('workspaces')->where('user_id', $request->user()->id)->find($workspace);
        abort_unless($space, 404);
        try {
            $base = $repo->cleanHead($space->path);
        } catch (\RuntimeException $e) {
            abort(409, $e->getMessage());
        }
        try {
            $id = DB::transaction(function () use ($workspace, $task, $base) {
                $id = DB::table('agent_runs')->insertGetId(['workspace_id' => $workspace, 'task' => $task,
                    'base' => $base, 'active' => true, 'status' => 'queued', 'created_at' => now(), 'updated_at' => now()]);
                RunCodingAgent::dispatch($id)->onQueue('harness')->afterCommit();
                return $id;
            });
        } catch (UniqueConstraintViolationException) {
            abort(409, 'Selesaikan run aktif terlebih dahulu.');
        }
        return response()->json(['id' => $id], 202);
    }

    private function owned(Request $request, int $id): object
    {
        $run = DB::table('agent_runs')->join('workspaces', 'workspaces.id', '=', 'agent_runs.workspace_id')
            ->where('workspaces.user_id', $request->user()->id)->where('agent_runs.id', $id)
            ->select('agent_runs.*', 'workspaces.path')->first();
        abort_unless($run, 404);
        return $run;
    }

    public function show(Request $request, int $run)
    {
        $row = $this->owned($request, $run);
        return response()->json(['id' => $run, 'status' => $row->status, 'task' => $row->task, 'patch' => $row->patch,
            'plan' => json_decode($row->plan ?? 'null'), 'checks' => json_decode($row->checks ?? '{}'),
            'evidence' => json_decode($row->evidence ?? 'null'), 'revision' => $row->revision,
            'question' => $row->question, 'phase' => $row->phase, 'stop_reason' => $row->stop_reason,
            'changes' => json_decode($row->changes ?? '[]'),
            'events' => DB::table('run_events')->where('agent_run_id', $run)->where('id', '>', $request->integer('after'))
                ->orderBy('id')->get(['id', 'kind', 'message', 'exit_code'])]);
    }

    public function action(Request $request, int $run, string $action, Repository $repo)
    {
        return DB::transaction(function () use ($request, $run, $action, $repo) {
            $row = $this->owned($request, $run);
            $query = DB::table('agent_runs')->where('id', $run);
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
                RunCodingAgent::dispatch($run)->onQueue('harness')->afterCommit();
                return response()->noContent();
            }
            $allowed = $action === 'apply' ? ['verified'] : ['verified', 'review', 'conflict', 'awaiting_input'];
            abort_unless($query->whereIn('status', $allowed)->update(['status' => 'deciding']), 409);
            if ($action === 'apply') {
                try {
                    if ($row->revision !== hash('sha256', (string) $row->patch)) {
                        throw new \RuntimeException('Patch berubah.');
                    }
                    if ($repo->cleanHead($row->path) !== $row->base) {
                        throw new \RuntimeException('HEAD berubah.');
                    }
                    if ($row->patch) {
                        $repo->git($row->path, ['apply', '--whitespace=nowarn', '-'], $row->patch);
                    }
                } catch (\RuntimeException) {
                    DB::table('agent_runs')->where('id', $run)->update(['status' => 'conflict']);
                    return response()->json(['message' => 'Workspace berubah atau patch tidak dapat diterapkan.'], 409);
                }
            }
            DB::table('agent_runs')->where('id', $run)->update(['status' => $action === 'apply' ? 'applied' : 'rejected', 'active' => null]);
            return response()->noContent();
        });
    }
}
