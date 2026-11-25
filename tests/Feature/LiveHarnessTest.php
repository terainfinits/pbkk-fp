<?php
// Fitur: Harness tugas coding
// connect with ../../app/Console/Commands/RegisterWorkspace.php, ../../app/Models/User.php, ../../app/Services/Harness/Repository.php
// connect with ../../config/services.php, ../../database/migrations/2026_11_14_000000_create_harness_tables.php, ../../routes/harness.php

use App\Models\User;
use App\Services\Harness\Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

it('asks Senopati to repair a real repository and checks it in Docker before approval', function () {
    $settings = Dotenv\Dotenv::createArrayBacked(base_path())->load();
    $endpoint = $settings['SENOPATI_ENDPOINT'] ?? config('services.senopati.endpoint');
    config(['services.senopati.endpoint' => $endpoint, 'services.senopati.api_key' => $settings['SENOPATI_API_KEY'] ?? '',
        'chat_ai.senopati_model' => $settings['SENOPATI_MODEL'] ?? 'qwen3:14b', 'queue.default' => 'database',
        'app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    if (!filled(config('services.senopati.api_key'))) {
        throw new RuntimeException('SENOPATI_API_KEY kosong.');
    }
    $root = storage_path('framework/testing/live-'.bin2hex(random_bytes(8)));
    $dir = $root.'/demo';
    File::ensureDirectoryExists($dir);
    $repo = new class($root) extends Repository {
        public function __construct(private string $root) {}
        public function tree(int $id): string { return $this->root.'/worktree-'.$id; }
    };
    app()->instance(Repository::class, $repo);
    try {
        $repo->git($dir, ['init']);
        file_put_contents($dir.'/sum.php', "<?php\nfunction sum(int \$a, int \$b): int { return \$a - \$b; }\n");
        file_put_contents($dir.'/tests.php', "<?php\nrequire __DIR__.'/sum.php';\nexit(sum(2, 3) === 5 ? 0 : 1);\n");
        $repo->git($dir, ['add','.']);
        $repo->git($dir, ['-c','user.name=Demo','-c','user.email=demo@example.test','commit','-m','fixture']);
        $user = User::factory()->create();
        $this->artisan('harness:register', ['path'=>$dir,'email'=>$user->email,'image'=>'harness-php:local'])->assertSuccessful();
        $space = DB::table('workspaces')->first();
        $url = '/ide/api/harness';
        $id = $this->actingAs($user)->postJson("$url/workspaces/$space->id/runs", ['task'=>'Inspect sum.php and tests.php. Run the failing test first. Fix sum and add a negative-number assertion to tests.php while preserving the original check. Run tests again, then finish.'])->assertAccepted()->json('id');
        $this->artisan('queue:work', ['connection'=>'database','--queue'=>'harness','--once'=>true,'--tries'=>1])->assertSuccessful();
        $run = $this->getJson("$url/runs/$id")->assertOk()->json();
        fwrite(STDERR, json_encode(array_map(fn ($e) => [$e['kind'], $e['kind'] === 'test' ? $e['exit_code'] : $e['message']], $run['events'])).PHP_EOL);
        expect($run['status'])->toBe('verified');
        expect(array_column($run['changes'], 'path'))->toEqualCanonicalizing(['sum.php','tests.php']);
        $codes = collect($run['events'])->where('kind','test')->pluck('exit_code');
        expect($codes->first())->toBe(1)->and($codes->last())->toBe(0);
        $repo->cleanHead($dir);
        $this->postJson("$url/runs/$id/apply")->assertNoContent();
        expect($repo->git($dir, ['diff','--name-only']))->toContain('sum.php','tests.php');
    } finally {
        File::deleteDirectory($root);
    }
})->skip(getenv('HARNESS_LIVE_TEST') !== '1', 'Requires Senopati credentials and a local Docker runner.');
