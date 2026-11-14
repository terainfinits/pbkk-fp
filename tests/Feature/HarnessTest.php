<?php

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
        foreach ([['tool'=>'search','text'=>'old'], ['tool'=>'read','path'=>'answer.txt'], ['tool'=>'test'], ['tool'=>'patch','patch'=>$patch], ['tool'=>'test'], ['tool'=>'finish']] as $action) {
            $sequence->push(['choices'=>[['message'=>['content'=>json_encode($action)]]]]);
        }
        Http::fake(['*'=>$sequence]);
        $tools = new class($repo) extends Tools {
            public function test(string $dir, object $space, int $id, callable $check): array
            {
                expect(file_exists($dir.'/.git'))->toBeFalse();
                return file_exists($dir.'/check.txt') ? [0, 'ok'] : [1, 'missing file'];
            }
        };
        (new RunCodingAgent($id))->handle($repo, $tools);
        Http::assertSent(fn ($request) => $request->url() === 'https://senopati.test/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && $request['model'] === config('chat_ai.senopati_model'));
        $this->getJson("$url/runs/$id")->assertOk()->assertJsonPath('status', 'review');
        expect(DB::table('run_events')->where('kind','test')->orderBy('id')->pluck('exit_code')->all())->toBe([1, 0]);
        expect(file_get_contents($dir.'/answer.txt'))->toBe("old\n");
        $this->actingAs(User::factory()->create())->postJson("$url/runs/$id/apply")->assertNotFound();
        $this->actingAs($user);
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
})->with(['apply', 'reject', 'conflict', 'cancel']);
