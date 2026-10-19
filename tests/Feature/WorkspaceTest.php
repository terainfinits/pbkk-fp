<?php

use App\Models\User;
use App\Services\AgentIde\WorkspaceService;

test('users have independent file trees and cannot access legacy files', function () {
    $ada = User::factory()->create();
    $grace = User::factory()->create();
    $this->actingAs($ada)->postJson('/ide/api/file/save', ['path' => 'private.py', 'content' => "print('Ada')"])->assertOk();
    $this->actingAs($grace)->getJson('/ide/api/tree')->assertOk()->assertJsonCount(0, 'tree');
    $this->postJson('/ide/api/file/read', ['path' => 'private.py'])->assertNotFound();
    $this->postJson('/ide/api/file/delete', ['path' => 'private.py'])->assertNotFound();
    $this->postJson('/ide/api/file/save', ['path' => 'private.py', 'content' => "print('Grace')"])->assertOk();
    $this->actingAs($ada)->postJson('/ide/api/file/read', ['path' => 'private.py'])->assertOk()->assertJsonPath('content', "print('Ada')");
});

test('file operations reject traversal absolute paths and workspace root deletion', function () {
    $this->actingAs(User::factory()->create());
    foreach (['../other/secret.py', '..\\other\\secret.py', '/etc/passwd', 'C:\\secret', '.'] as $path) {
        $this->postJson('/ide/api/file/read', ['path' => $path])->assertUnprocessable();
        $this->postJson('/ide/api/file/save', ['path' => $path, 'content' => 'bad'])->assertUnprocessable();
        $this->postJson('/ide/api/file/delete', ['path' => $path])->assertUnprocessable();
        $this->postJson('/ide/api/file/create', ['path' => $path, 'type' => 'file'])->assertUnprocessable();
    }
});

test('file and AI context operations reject symlinks outside the workspace', function () {
    $this->actingAs(User::factory()->create());
    $root = app(WorkspaceService::class)->root();
    $outside = dirname($root).'/outside';
    mkdir($outside);
    file_put_contents($outside.'/secret', 'private');
    symlink($outside, $root.'/link');
    $this->getJson('/ide/api/tree')->assertJsonCount(0, 'tree');
    foreach (['read', 'save', 'delete', 'create'] as $action) {
        $this->postJson('/ide/api/file/'.$action, ['path' => 'link/secret', 'type' => 'file', 'content' => 'bad'])->assertForbidden();
    }
    $this->postJson('/ide/api/agent/prompt', ['provider' => 'ollama_cloud', 'model' => 'demo', 'prompt' => 'write Python', 'targetFile' => 'link/secret'])->assertForbidden();
    expect(file_get_contents($outside.'/secret'))->toBe('private');
});

test('file operations preserve blank content and support directories and deletion', function () {
    $this->actingAs(User::factory()->create());
    $this->postJson('/ide/api/file/create', ['path' => 'src', 'type' => 'directory'])->assertOk();
    $this->postJson('/ide/api/file/create', ['path' => 'src/main.py', 'type' => 'file', 'content' => ''])->assertOk();
    $this->postJson('/ide/api/file/save', ['path' => 'src/main.py', 'content' => '  preserved  '])->assertOk();
    $this->postJson('/ide/api/file/read', ['path' => 'src/main.py'])->assertJsonPath('content', '  preserved  ');
    $this->postJson('/ide/api/file/save', ['path' => 'src/main.py', 'content' => ''])->assertOk();
    $this->postJson('/ide/api/file/read', ['path' => 'src/main.py'])->assertJsonPath('content', '');
    $this->postJson('/ide/api/file/delete', ['path' => 'src'])->assertOk();
    $this->getJson('/ide/api/tree')->assertJsonCount(0, 'tree');
});
