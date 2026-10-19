<?php

use App\Models\User;
use App\Services\AgentIde\RuntimeService;
use App\Services\AgentIde\SandboxService;
use App\Services\AgentIde\WorkspaceService;

test('docker driver preserves Windows paths as arguments and mounts only the private workspace', function () {
    config(['ide.sandbox_driver' => 'docker', 'ide.docker_binary' => PHP_BINARY]);
    $workspace = Mockery::mock(WorkspaceService::class);
    $workspace->shouldReceive('root')->once()->andReturn('C:\\Users\\Ada Lovelace\\Magentic\\users\\1');
    $workspace->shouldReceive('normalize')->with('', true)->andReturn('');
    $workspace->shouldReceive('resolve')->with('', true)->andReturn(sys_get_temp_dir());
    $sandbox = new SandboxService($workspace, app(RuntimeService::class));
    $command = $sandbox->command(['/usr/bin/python3', '-u', '/run/code'], '', 'C:\\Magentic Runs\\code.py');
    expect($command)->toContain(
        '--network=none', '--cap-drop=ALL', '--read-only', '--memory=512m', '--pids-limit=128',
        'type=bind,source=C:\\Users\\Ada Lovelace\\Magentic\\users\\1,target=/workspace',
        'type=bind,source=C:\\Magentic Runs\\code.py,target=/run/code,readonly'
    );
    expect(array_slice($command, -3))->toBe(['/usr/bin/python3', '-u', '/run/code']);
    $cleanup = $sandbox->cleanupCommand($command);
    expect($cleanup[0])->toBe(PHP_BINARY);
    expect(array_slice($cleanup, 1, 2))->toBe(['rm', '--force']);
    expect($cleanup[3])->toStartWith('magentic-');
    expect($sandbox->cleanupCommand(['/usr/bin/bwrap', '--unshare-all']))->toBe([]);
});

test('missing Docker disables execution with setup guidance instead of using native processes', function () {
    $this->actingAs(User::factory()->create());
    config(['ide.sandbox_driver' => 'docker', 'ide.docker_binary' => '/nonexistent/docker']);
    $this->postJson('/ide/api/code/python/start', ['code' => 'print(1)'])->assertStatus(503)->assertJsonPath('message', 'Start Docker Desktop in Linux-container mode and build the Magentic runtime image. See README.md.');
    $this->postJson('/ide/api/file/save', ['path' => 'example.py', 'content' => 'print(1)'])->assertOk();
});
