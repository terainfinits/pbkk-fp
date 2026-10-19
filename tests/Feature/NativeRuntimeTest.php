<?php

use App\Models\User;
use App\Services\AgentIde\RuntimeService;
use App\Services\AgentIde\SandboxService;
use App\Services\AgentIde\WorkspaceService;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('local execution defaults to native and works without Docker or Bubblewrap', function () {
    $this->actingAs(User::factory()->create());
    expect(config('ide.sandbox_driver'))->toBe('native');
    config(['ide.sandbox_binary' => '/missing/bwrap', 'ide.docker_binary' => '/missing/docker']);
    file_put_contents(app(WorkspaceService::class)->root().'/helper.py', 'answer = "native"');
    $this->postJson('/ide/api/code/run', ['language' => 'python', 'code' => 'import helper; print(helper.answer)'])->assertOk()->assertJsonPath('exitCode', 0)->assertJsonPath('stdout', "native\n");
});

test('native execution is rejected in production without fallback', function () {
    $this->actingAs(User::factory()->create());
    $this->app->detectEnvironment(fn () => 'production');
    $this->withSession(['_token' => 'production-test-token'])->withHeader('X-CSRF-TOKEN', 'production-test-token');
    config(['ide.sandbox_driver' => 'native']);
    foreach (['code/python/start', 'code/run'] as $endpoint) {
        $this->postJson('/ide/api/'.$endpoint, ['language' => 'python', 'code' => 'print(1)'])->assertStatus(503);
    }
    $this->postJson('/ide/api/terminal/execute', ['command' => 'pwd'])->assertStatus(503);
});

test('file editing does not start Python or Docker', function () {
    $this->actingAs(User::factory()->create());
    config(['ide.python_binary' => '/missing/python', 'ide.sandbox_driver' => 'docker', 'ide.docker_binary' => '/missing/docker']);
    $this->postJson('/ide/api/file/save', ['path' => 'folder space/你好.py', 'content' => "print('hello')"])->assertOk();
    $this->postJson('/ide/api/file/read', ['path' => 'folder space/你好.py'])->assertOk()->assertJsonPath('content', "print('hello')");
    $this->getJson('/ide/api/tree')->assertOk()->assertJsonPath('tree.0.children.0.name', '你好.py');
});

test('missing Python returns an actionable error', function () {
    $this->actingAs(User::factory()->create());
    config(['ide.python_binary' => '/missing/python']);
    $this->postJson('/ide/api/code/run', ['language' => 'python', 'code' => 'print(1)'])->assertUnprocessable();
    $this->postJson('/ide/api/code/python/start', ['code' => 'print(1)'])->assertUnprocessable();
});

test('Windows Python launcher detection retains its interpreter argument', function () {
    $runtime = Mockery::mock(RuntimeService::class)->makePartial()->shouldAllowMockingProtectedMethods();
    $runtime->shouldReceive('isWindows')->andReturn(true);
    $runtime->shouldReceive('locate')->with('python')->andReturn(null);
    $runtime->shouldReceive('locate')->with('py')->andReturn('C:\\Program Files\\Python Launcher\\py.exe');
    $runtime->shouldReceive('probe')->once()->with(['C:\\Program Files\\Python Launcher\\py.exe', '-3'])->andReturn('Python 3.13.0');
    expect($runtime->require('python'))->toBe(['C:\\Program Files\\Python Launcher\\py.exe', '-3']);
    expect($runtime->require('python'))->toBe(['C:\\Program Files\\Python Launcher\\py.exe', '-3']);
});

test('Windows native commands preserve paths and select PowerShell', function () {
    $runtime = Mockery::mock(RuntimeService::class)->makePartial();
    $runtime->shouldReceive('isWindows')->andReturn(true);
    $runtime->shouldReceive('require')->with('shell')->andReturn(['C:\\Windows\\PowerShell\\powershell.exe']);
    $workspace = Mockery::mock(WorkspaceService::class);
    $workspace->shouldReceive('root')->andReturn('C:\\Magentic Space\\users\\1');
    $workspace->shouldReceive('normalize')->with('', true)->andReturn('');
    $workspace->shouldReceive('resolve')->with('', true)->andReturn(sys_get_temp_dir());
    $runner = new SandboxService($workspace, $runtime);
    $arguments = ['C:\\Python Space\\python.exe', '-u', '/run/code'];
    expect($runner->command($arguments, '', 'C:\\Run Space\\hello.py'))->toBe(['C:\\Python Space\\python.exe', '-u', 'C:\\Run Space\\hello.py']);
    expect($runner->shellInfo())->toBe(['name' => 'PowerShell', 'platform' => 'windows']);
    expect($runner->shellCommand('Get-Location'))->toContain('-NoProfile', '-Command');
});

test('native shell executes in the private workspace and navigates directories with spaces', function () {
    $this->actingAs(User::factory()->create());
    $root = app(WorkspaceService::class)->root();
    mkdir($root.'/folder space');
    $this->postJson('/ide/api/terminal/execute', ['command' => 'cd "folder space"'])->assertOk()->assertJsonPath('cwd', 'folder space');
    $command = PHP_OS_FAMILY === 'Windows' ? '[Console]::Write((Get-Location).Path)' : 'pwd';
    $result = $this->postJson('/ide/api/terminal/execute', ['command' => $command, 'cwd' => 'folder space'])->assertOk()->assertJsonPath('exitCode', 0);
    expect(str_replace('\\', '/', trim($result->json('stdout'))))->toBe(str_replace('\\', '/', realpath($root.'/folder space')));
    $this->postJson('/ide/api/terminal/execute', ['command' => 'cd ..', 'cwd' => 'folder space'])->assertJsonPath('cwd', '');
    $this->postJson('/ide/api/terminal/execute', ['command' => 'cd ..'])->assertUnprocessable();
    $this->getJson('/ide/api/kernels')->assertJsonPath('shell.platform', PHP_OS_FAMILY === 'Windows' ? 'windows' : 'linux');
});

test('Windows workspace rules reject reserved names UNC paths and trailing dots', function () {
    $runtime = Mockery::mock(RuntimeService::class)->makePartial();
    $runtime->shouldReceive('isWindows')->andReturn(true);
    $this->app->instance(RuntimeService::class, $runtime);
    $workspace = app(WorkspaceService::class);
    foreach (['CON', 'nul.txt', 'COM1.py', 'folder/file.', 'folder/file ', 'file:stream', '\\\\server\\share'] as $path) {
        try {
            $workspace->normalize($path);
            test()->fail('Invalid path was accepted: '.$path);
        } catch (HttpException $error) {
            expect($error->getStatusCode())->toBe(422);
        }
    }
});
