<?php
// Fitur: Harness tugas coding
// connect with ../../app/Console/Commands/RegisterWorkspace.php, ../../app/Jobs/RunCodingAgent.php, ../../app/Models/User.php
// connect with ../../app/Services/Harness/Repository.php, ../../app/Services/Harness/Tools.php, ../../config/chat_ai.php
// connect with ../../database/migrations/2026_11_14_000000_create_harness_tables.php, ../../routes/harness.php

use App\Jobs\RunCodingAgent;
use App\Models\User;
use App\Services\Harness\Repository;
use App\Services\Harness\Tools;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('reviews a two-file change and only applies it to an unchanged owned repository', function (string $decision) {
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    config(['services.senopati.api_key' => 'test-key', 'services.senopati.endpoint' => 'https://senopati.test/chat/completions']);
    $repo = new class($decision === 'folder-auto-apply') extends Repository {
        private string $prefix;
        public function __construct(private bool $folder) { $this->prefix = uniqid('test-worktree-'); }
        public function tree(int $id): string { return storage_path('framework/testing/'.$this->prefix.'-'.$id); }
        public function checkout(object $run, object $workspace): string
        {
            if (!$this->folder) return parent::checkout($run, $workspace);
            $dir = $this->tree($run->id);
            app(\App\Services\Harness\FolderWorkspace::class)->snapshot($this, $workspace->path, $dir);
            return $dir;
        }
        public function discard(object $workspace, int $id): void
        {
            if ($this->folder) File::deleteDirectory($this->tree($id));
            else parent::discard($workspace, $id);
        }
    };
    app()->instance(Repository::class, $repo);
    $dir = storage_path('framework/testing/repo-'.uniqid());
    File::ensureDirectoryExists($dir);
    try {
        $repo->git($dir, ['init']);
        file_put_contents($dir.'/answer.txt', "old\n");
        $repo->git($dir, ['add', 'answer.txt']);
        $repo->git($dir, ['-c','user.name=Tester','-c','user.email=test@example.test','commit','-m','base']);
        $user = User::factory()->create();
        $this->artisan('harness:register', ['path' => $dir, 'email' => $user->email, 'image' => 'test:local'])->assertSuccessful();
        $space = DB::table('workspaces')->first();
        Queue::fake();
        if ($decision === 'retry-worker' || $decision === 'clarify') {
            config(['queue.harness_mode'=>'local']);
            $launcher = new class extends \App\Services\Harness\WorkerLauncher {
                public int $calls = 0;
                public bool $fail = false;
                protected function launch(int $run): void {
                    ++$this->calls;
                    if ($this->fail) throw new RuntimeException('Simulated launch failure');
                }
            };
            $launcher->fail = $decision === 'retry-worker';
            app()->instance(\App\Services\Harness\WorkerLauncher::class, $launcher);
        }
        $url = '/ide/api/harness';
        $this->actingAs($user);
        $id = $this->postJson("$url/workspaces/$space->id/runs", ['task' => 'Update answer'])->assertAccepted()->json('id');
        $this->postJson("$url/workspaces/$space->id/runs", ['task' => 'Duplicate'])->assertConflict();
        if ($decision === 'empty-review') {
            DB::table('agent_runs')->where('id', $id)->update(['status' => 'review', 'patch' => '']);
            $next = $this->postJson("$url/workspaces/$space->id/runs", ['task' => 'Try again'])
                ->assertAccepted()->json('id');
            $this->assertDatabaseHas('agent_runs', ['id' => $id, 'status' => 'review', 'active' => null]);
            DB::table('agent_runs')->where('id', $next)->update(['status' => 'review', 'patch' => 'pending patch']);
            $this->postJson("$url/workspaces/$space->id/runs", ['task' => 'Duplicate'])->assertConflict();
            return;
        }
        if ($decision === 'retry-worker') {
            $this->getJson("$url/runs/$id")->assertJsonPath('stop_reason','worker_launch_failed');
            $launcher->fail = false;
            $this->postJson("$url/runs/$id/retry-worker")->assertNoContent();
            $this->getJson("$url/runs/$id")->assertJsonPath('stop_reason',null)->assertJsonPath('status','queued');
            Queue::assertPushed(RunCodingAgent::class,1);
            expect($launcher->calls)->toBe(2);
            return;
        }
        if ($decision === 'cancel') {
            $this->postJson("$url/runs/$id/cancel")->assertNoContent();
            (new RunCodingAgent($id))->handle($repo, app(Tools::class));
            $this->getJson("$url/runs/$id")->assertOk()->assertJsonPath('status', 'cancelled');
            expect(is_dir($repo->tree($id)))->toBeFalse();
            return;
        }
        $patch = "--- a/answer.txt\n+++ b/answer.txt\n@@ -1 +1 @@\n-old\n+new\n--- /dev/null\n+++ b/check.txt\n@@ -0,0 +1 @@\n+ok\n";
        if ($decision === 'disconnect') {
            $provider = new class($id) extends \App\Services\Harness\SenopatiAgent {
                public int $calls = 0;
                public function __construct(private int $id) {}
                public function next(array $messages, int $timeout): string
                {
                    $last = DB::table('run_events')->where('agent_run_id', $this->id)->latest('id')->first();
                    expect($last->kind)->toBe('provider_wait');
                    if (++$this->calls === 1) {
                        sleep(20);
                    }
                    if ($this->calls === 3) {
                        throw new RuntimeException('Waktu tunggu Senopati habis (cURL 28).');
                    }
                    return '{"tool":"read","path":"answer.txt","progress":"Memeriksa masukan"}';
                }
            };
            app()->instance(\App\Services\Harness\SenopatiAgent::class, $provider);
            (new RunCodingAgent($id))->handle($repo, app(Tools::class));
            $events = collect($this->getJson("$url/runs/$id")->assertJsonPath('status', 'failed')->json('events'));
            expect($events->where('kind','trace_instruction')->count())->toBe(3)
                ->and($events->where('kind','trace_reply')->count())->toBe(2)
                ->and($events->where('kind','trace_reply')->first()['message'])->toContain('"path":"answer.txt"')
                ->and($events->where('kind','trace_result')->first()['message'])->toContain('old');
            expect($events->where('kind','completed')->count())->toBe(2)
                ->and($events->where('kind','requested')->first()['message'])->toBe('Memeriksa masukan')
                ->and($events->where('kind','provider_done')->first()['duration_ms'])->toBeGreaterThanOrEqual(20000)
                ->and($events->where('kind','provider_failed')->first()['message'])->toContain('cURL 28')
                ->and($events->where('kind','test')->count())->toBe(0);
            $cursor = $events->last()['id'];
            $this->getJson("$url/runs/$id?after=$cursor")->assertJsonCount(0, 'events');
            $this->getJson("$url/workspaces/$space->id/runs")->assertOk()->assertJsonPath('data.0.id', $id);
            $this->actingAs(User::factory()->create())->getJson("$url/workspaces/$space->id/runs")->assertNotFound();
            return;
        }
        $sequence = Http::sequence();
        $finish = ['tool'=>'finish', 'summary'=>'Updated answer', 'evidence'=>['Fixture check passed']];
        $plan = ['tool'=>'plan', 'goal'=>'Update answer', 'files'=>['answer.txt'], 'steps'=>['Edit then test'],
            'criteria'=>[['description'=>'New answer', 'check'=>'test']]];
        $actions = [['tool'=>'search','text'=>'old'], $plan, ['tool'=>'test'], ['tool'=>'patch','patch'=>$patch], ['tool'=>'test'], $finish];
        if ($decision === 'clarify') {
            $this->app->instance(\App\Services\Harness\SenopatiAgent::class, new class extends \App\Services\Harness\SenopatiAgent {
                public function next(array $messages, int $timeout): string
                {
                    return '{"tool":"ask","question":"Which file?"}';
                }
            });
            (new RunCodingAgent($id))->handle($repo, app(Tools::class));
            $this->getJson("$url/runs/$id")->assertJsonPath('status', 'awaiting_input');
            $this->postJson("$url/runs/$id/answer", ['answer'=>'answer.txt'])->assertNoContent();
            expect($launcher->calls)->toBe(2);
            $this->app->forgetInstance(\App\Services\Harness\SenopatiAgent::class);
        } elseif ($decision === 'baseline') {
            $actions = [$actions[0], $plan, ['tool'=>'patch','patch'=>$patch], ['tool'=>'patch','patch'=>$patch], $finish, $finish];
        } elseif ($decision === 'edit') {
            $actions = [$actions[0], $plan, ['tool'=>'test'], ['tool'=>'edit','path'=>'answer.txt','old'=>'old','new'=>'new'],
                ['tool'=>'patch','patch'=>"--- /dev/null\n+++ b/check.txt\n@@ -0,0 +1 @@\n+ok\n"], $finish, $finish];
        } elseif ($decision === 'manual') {
            $actions[1]['criteria'][0]['check'] = 'manual';
        } elseif ($decision === 'extra') {
            DB::table('workspaces')->where('id', $space->id)->update(['extra_checks'=>'{"build":"build:local"}']);
            array_push($actions, $finish);
        } elseif ($decision === 'early') {
            $actions = array_fill(0, 3, $finish);
        } elseif ($decision === 'failures') {
            $actions = [$actions[0], $plan, $finish, $finish, $finish];
        } elseif ($decision === 'limit') {
            $actions = array_fill(0, 20, ['tool'=>'list']);
        } elseif ($decision === 'stale') {
            array_pop($actions);
            $actions[] = ['tool'=>'patch','patch'=>"--- a/check.txt\n+++ /dev/null\n@@ -1 +0,0 @@\n-ok\n"];
            array_push($actions, $finish, $finish);
        }
        foreach ($actions as $action) {
            $sequence->push(['choices'=>[['message'=>['content'=>json_encode($action)]]]]);
        }
        Http::fake(['*'=>$sequence]);
        $tools = new class($repo) extends Tools {
            public function test(string $dir, object $space, int $id, callable $check): array
            {
                expect(file_exists($dir.'/.git'))->toBeFalse();
                if ($space->image === 'build:local') {
                    return [1, 'build failed'];
                }
                return file_exists($dir.'/check.txt') ? [0, 'ok'] : [1, 'missing file'];
            }
        };
        if ($decision === 'folder-auto-apply') {
            DB::table('agent_runs')->where('id', $id)->update(['base' => 'folder']);
        }
        (new RunCodingAgent($id))->handle($repo, $tools);
        Http::assertSent(fn ($request) => $request->url() === 'https://senopati.test/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && $request['model'] === config('chat_ai.senopati_model'));
        $reasons = ['early'=>'invalid_actions', 'failures'=>'test_failures', 'limit'=>'step_limit', 'stale'=>'test_failures',
            'manual'=>'manual_check_required', 'extra'=>'test_failures'];
        if (isset($reasons[$decision])) {
            $this->getJson("$url/runs/$id")->assertJsonPath('status', 'review')->assertJsonPath('stop_reason', $reasons[$decision]);
            $this->postJson("$url/runs/$id/apply")->assertConflict();
            expect(file_get_contents($dir.'/answer.txt'))->toBe("old\n");
            return;
        }
        if ($decision === 'folder-auto-apply') {
            $this->getJson("$url/runs/$id")->assertOk()->assertJsonPath('status', 'applied');
            expect(str_replace("\r\n", "\n", file_get_contents($dir.'/answer.txt')))->toBe("new\n")
                ->and(file_exists($dir.'/check.txt'))->toBeTrue();
            return;
        }
        $this->getJson("$url/runs/$id")->assertOk()->assertJsonPath('status', 'verified');
        expect(DB::table('run_events')->where('kind','test')->orderBy('id')->pluck('exit_code')->all())->toBe([1, 0]);
        expect(file_get_contents($dir.'/answer.txt'))->toBe("old\n");
        $this->actingAs(User::factory()->create())->postJson("$url/runs/$id/apply")->assertNotFound();
        $this->actingAs($user);
        if ($decision === 'clarify') {
            Http::assertSent(fn ($request) => collect($request['messages'])->contains(fn ($m) => str_contains($m['content'], 'Which file?')));
            $decision = 'apply';
        }
        if ($decision === 'baseline') {
            $events = DB::table('run_events')->where('agent_run_id', $id)->orderBy('id')->get();
            $deferred = $events->firstWhere('kind','deferred')->id;
            $baseline = $events->firstWhere('kind','test')->id;
            $patched = $events->firstWhere('message','Patch berhasil diterapkan')->id;
            expect($deferred)->toBeLessThan($baseline)->and($baseline)->toBeLessThan($patched);
            $decision = 'apply';
        }
        if ($decision === 'edit') {
            $decision = 'apply';
        }
        if ($decision === 'conflict') {
            file_put_contents($dir.'/answer.txt', 'user change');
            $this->postJson("$url/runs/$id/apply")->assertConflict();
            expect(file_get_contents($dir.'/answer.txt'))->toBe('user change');
            $decision = 'reject';
        }
        $this->postJson("$url/runs/$id/$decision")->assertNoContent();
        expect(file_exists($dir.'/check.txt'))->toBe($decision === 'apply');
    } finally {
        File::deleteDirectory($dir);
    }
})->with(['apply', 'reject', 'conflict', 'cancel', 'early', 'failures', 'limit', 'stale', 'manual', 'extra', 'clarify', 'edit', 'disconnect', 'baseline', 'retry-worker', 'empty-review', 'folder-auto-apply']);

it('keeps a valid action when optional progress is unusable', function ($progress) {
    $action = \App\Services\Harness\AgentAction::parse(json_encode(['tool'=>'list','progress'=>$progress]), ['test']);
    expect($action)->toBe(['tool'=>'list']);
})->with([[null], ['  '], [str_repeat('a',161)], [['bad']]]);

it('distinguishes DNS, connection and timeout failures', function ($errno, $label) {
    $exception = new \GuzzleHttp\Exception\ConnectException("cURL error $errno: transport failed",
        new \GuzzleHttp\Psr7\Request('POST','https://example.test'));
    expect(\App\Services\SenopatiConnection::describe(new RuntimeException('wrapped',0,$exception)))->toContain($label);
})->with([[6,'DNS'],[7,'dibuka'],[28,'habis']]);
