<?php

use App\Models\User;
use App\Services\AgentIde\SandboxService;
use App\Services\AgentIde\WorkspaceService;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('ide renders the authenticated Vue application', function () {
    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->component('AgentIdeApp')->has('auth.user.id')->missing('auth.user.password'));
    $this->get('/ide')->assertRedirect('/');
});

test('kernel detection reports available runtimes', function () {
    $this->getJson(route('ide.api.kernels'))->assertOk()->assertJsonStructure(['success', 'kernels' => ['python', 'php', 'node']]);
});

test('code runner executes supported languages inside isolation', function (string $language, string $code, string $output) {
    if (! app(SandboxService::class)->available()) {
        $this->markTestSkipped('Bubblewrap namespaces unavailable.');
    }
    $this->postJson(route('ide.api.code.run'), ['code' => $code, 'language' => $language])->assertOk()->assertJsonPath('exitCode', 0)->assertJsonPath('stdout', $output);
})->with([
    ['python', 'print("Magentic Python")', "Magentic Python\n"],
    ['php', '<?php echo "Magentic PHP";', 'Magentic PHP'],
    ['node', 'console.log("Magentic Node")', "Magentic Node\n"],
]);

test('execution fails closed when isolation is unavailable', function () {
    config(['ide.sandbox_binary' => '/missing/bwrap']);
    $this->postJson(route('ide.api.code.run'), ['code' => 'print(1)', 'language' => 'python'])->assertStatus(503);
    $this->postJson(route('ide.api.python.start'), ['code' => 'print(1)'])->assertStatus(503);
    $this->postJson(route('ide.api.terminal.execute'), ['command' => 'pwd'])->assertStatus(503);
    $this->postJson(route('ide.api.file.save'), ['path' => 'still-works.py', 'content' => 'print(1)'])->assertOk();
});

test('isolated code cannot see application secrets other users or host environment and has no network', function () {
    if (! app(SandboxService::class)->available()) {
        $this->markTestSkipped('Bubblewrap namespaces unavailable.');
    }
    $workspace = app(WorkspaceService::class)->root();
    file_put_contents($workspace.'/my-file.txt', 'mine');
    $code = <<<'PYTHON'
import os, socket
assert open('/workspace/my-file.txt').read() == 'mine'
assert not os.path.exists('/home/sultangg/code/pbkk-fp/.env')
assert not os.path.exists('/workspace/../users')
assert 'APP_KEY' not in os.environ
assert os.getuid() != 0
try:
    socket.create_connection(('1.1.1.1', 443), timeout=0.3)
except OSError:
    print('isolated')
else:
    raise AssertionError('network was reachable')
PYTHON;
    $this->postJson(route('ide.api.code.run'), ['code' => $code, 'language' => 'python'])->assertOk()->assertJsonPath('exitCode', 0)->assertJsonPath('stdout', "isolated\n");
    $this->postJson(route('ide.api.terminal.execute'), ['command' => 'pwd; test ! -e /home/sultangg/code/pbkk-fp/.env'])->assertOk()->assertJsonPath('stdout', "/workspace\n")->assertJsonPath('exitCode', 0);
});
