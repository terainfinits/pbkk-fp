<?php

use App\Models\User;
use App\Services\AgentIde\Projects;
use App\Services\Harness\Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

it('isolates identical files and chat histories by owned project', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $root = storage_path('framework/testing/projects-'.uniqid());
    $spaces = [];
    try {
        foreach (['game','sample','private'] as $name) {
            $path = $root.'/'.$name;
            File::ensureDirectoryExists($path);
            file_put_contents($path.'/app.js', $name);
            $spaces[$name] = DB::table('workspaces')->insertGetId(['path'=>$path,'name'=>$name,
                'image'=>'test:local','user_id'=>$name === 'private' ? $other->id : $user->id]);
        }
        $this->actingAs($user);
        $base = '/ide/api';
        $this->getJson("$base/projects")->assertOk()->assertJsonCount(3)->assertJsonPath('0.id','ide');
        foreach (['game','sample'] as $name) {
            $this->postJson("$base/file/read?project=$spaces[$name]",['path'=>'app.js'])->assertJsonPath('content',$name);
        }
        $game = "?project=$spaces[game]";
        $sample = "?project=$spaces[sample]";
        $this->postJson("$base/file/save".$game,['path'=>'app.js','content'=>'updated'])->assertOk();
        expect(file_get_contents("$root/sample/app.js"))->toBe('sample');
        \Illuminate\Support\Facades\Http::fake(['*'=>\Illuminate\Support\Facades\Http::response(['choices'=>[['message'=>['content'=>'Game project']]]])]);
        $this->postJson("$base/agent/prompt".$game, ['provider'=>'senopati','apiKey'=>'fixture-key','model'=>'qwen3:14b',
            'prompt'=>'Explain app.js','targetFile'=>'app.js'])->assertOk()->assertJsonPath('success',true);
        \Illuminate\Support\Facades\Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'],'Current Workspace Base: game')
            && str_contains($r['messages'][0]['content'],'updated'));
        $session = $this->postJson("$base/chats".$game,['title'=>'Game'])->assertCreated()->json('session.id');
        $this->getJson("$base/chats/$session".$sample)->assertNotFound();
        $this->getJson("$base/chats")->assertJsonCount(0,'sessions');
        $this->postJson("$base/agent/prompt".$game,['prompt'=>'Explain','targetFile'=>'../sample/app.js'])->assertUnprocessable();
        $this->postJson("$base/file/read?project=$spaces[private]",['path'=>'app.js'])->assertNotFound();
        $this->postJson("$base/file/save".$game,['path'=>'../sample/app.js','content'=>'oops'])->assertUnprocessable();
    } finally {
        File::deleteDirectory($root);
    }
});

it('explains each Git and runner prerequisite', function () {
    $dir = storage_path('framework/testing/ready-'.uniqid());
    File::ensureDirectoryExists($dir.'/nested');
    $projects = app(Projects::class);
    $git = app(Repository::class);
    try {
        $git->git($dir,['init']);
        expect($projects->reason($dir,null))->toContain('commit awal');
        expect($projects->reason($dir.'/nested',null))->toContain('subfolder');
        file_put_contents($dir.'/app.js','one');
        $git->git($dir,['add','.']);
        $git->git($dir,['-c','user.name=Test','-c','user.email=test@example.test','commit','-m','starter']);
        expect($projects->reason($dir,null))->toContain('runner')->and($projects->reason($dir,'test:local'))->toBeNull();
        file_put_contents($dir.'/app.js','two');
        expect($projects->reason($dir,'test:local'))->toContain('perubahan lokal');
    } finally {
        File::deleteDirectory($dir);
    }
});
