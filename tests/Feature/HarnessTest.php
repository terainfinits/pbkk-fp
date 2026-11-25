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
    $repo = app(Repository::class);
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
        $url = '/ide/api/harness';
        $this->actingAs($user);
        $id = $this->postJson("$url/workspaces/$space->id/runs", ['task' => 'Update answer'])->assertAccepted()->json('id');
        $this->postJson("$url/workspaces/$space->id/runs", ['task' => 'Duplicate'])->assertConflict();
        if ($decision === 'cancel') {
            $this->postJson("$url/runs/$id/cancel")->assertNoContent();
            (new RunCodingAgent($id))->handle($repo, app(Tools::class));
            $this->getJson("$url/runs/$id")->assertOk()->assertJsonPath('status', 'cancelled');
            expect(is_dir($repo->tree($id)))->toBeFalse();
            return;
        }
        $patch = "--- a/answer.txt\n+++ b/answer.txt\n@@ -1 +1 @@\n-old\n+new\n--- /dev/null\n+++ b/check.txt\n@@ -0,0 +1 @@\n+ok\n";
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
            $this->app->forgetInstance(\App\Services\Harness\SenopatiAgent::class);
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
        $this->getJson("$url/runs/$id")->assertOk()->assertJsonPath('status', 'verified');
        expect(DB::table('run_events')->where('kind','test')->orderBy('id')->pluck('exit_code')->all())->toBe([1, 0]);
        expect(file_get_contents($dir.'/answer.txt'))->toBe("old\n");
        $this->actingAs(User::factory()->create())->postJson("$url/runs/$id/apply")->assertNotFound();
        $this->actingAs($user);
        if ($decision === 'clarify') {
            Http::assertSent(fn ($request) => collect($request['messages'])->contains(fn ($m) => str_contains($m['content'], 'Which file?')));
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
})->with(['apply', 'reject', 'conflict', 'cancel', 'early', 'failures', 'limit', 'stale', 'manual', 'extra', 'clarify', 'edit']);
