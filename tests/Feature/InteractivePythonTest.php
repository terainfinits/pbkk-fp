<?php

use App\Models\User;
use App\Services\AgentIde\InteractivePythonService;
use App\Services\AgentIde\SandboxService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $token = bin2hex(random_bytes(16));
    $this->pythonOwner = $user->id.':'.$token;
    $this->pythonRuns = [];
    $this->withSession(['python_owner' => $token]);
    if (! app(SandboxService::class)->available()) {
        $this->markTestSkipped('Bubblewrap namespaces are unavailable in this environment.');
    }
});

afterEach(function () {
    $service = app(InteractivePythonService::class);
    foreach ($this->pythonRuns as $id) {
        $service->stop($id, $this->pythonOwner);
        $deadline = microtime(true) + 5;
        while ($service->status($id, $this->pythonOwner)['running'] && microtime(true) < $deadline) {
            usleep(25000);
        }
        File::deleteDirectory(config('ide.runs_root').'/'.$id);
    }
});

function awaitPythonState($test, string $id, callable $condition): array
{
    $deadline = microtime(true) + 5;
    do {
        $response = $test->getJson(route('ide.api.python.status', ['runId' => $id]))->assertOk();
        $state = $response->json();
        if ($condition($state)) {
            return $state;
        }
        usleep(25000);
    } while (microtime(true) < $deadline);

    throw new RuntimeException('Python did not reach the expected state: '.json_encode($state));
}

test('python keeps running across multiple input prompts without replaying code', function () {
    $code = <<<'PY'
print('started once')
name = input('Name: ')
print('Hello ' + name)
age = input('Age: ')
print('Next year:', int(age) + 1)
PY;
    $id = $this->postJson(route('ide.api.python.start'), ['code' => $code])
        ->assertOk()->json('runId');
    $this->pythonRuns[] = $id;
    $initial = awaitPythonState($this, $id, fn ($s) => str_contains($s['stdout'], 'Name: '));
    expect($initial['running'])->toBeTrue();

    $this->postJson(route('ide.api.python.input'), ['runId' => $id, 'input' => 'Ada'])->assertOk();
    $second = awaitPythonState($this, $id, fn ($s) => str_contains($s['stdout'], 'Age: '));
    expect($second['stdout'])->toContain('Hello Ada');
    expect($second['running'])->toBeTrue();

    $this->postJson(route('ide.api.python.input'), ['runId' => $id, 'input' => '20'])->assertOk();
    $final = awaitPythonState($this, $id, fn ($s) => ! $s['running']);
    expect($final['exitCode'])->toBe(0);
    expect($final['stdout'])->toContain('Next year: 21');
    expect(substr_count($final['stdout'], 'started once'))->toBe(1);
    $this->postJson(route('ide.api.python.input'), ['runId' => $id, 'input' => 'late'])->assertStatus(409);
});

test('python receives blank lines spaces unicode and shell text as literal stdin', function () {
    $code = "print(repr(input('First: ')))\nprint(repr(input('Second: ')))";
    $id = $this->postJson(route('ide.api.python.start'), ['code' => $code])->assertOk()->json('runId');
    $this->pythonRuns[] = $id;
    awaitPythonState($this, $id, fn ($s) => str_contains($s['stdout'], 'First: '));
    $this->postJson(route('ide.api.python.input'), ['runId' => $id, 'input' => ''])->assertOk();
    awaitPythonState($this, $id, fn ($s) => str_contains($s['stdout'], 'Second: '));
    $this->postJson(route('ide.api.python.input'), ['runId' => $id, 'input' => '  héllo; $(echo hi)  '])->assertOk();
    $final = awaitPythonState($this, $id, fn ($s) => ! $s['running']);
    expect($final['stdout'])->toContain("First: ''", "'  héllo; $(echo hi)  '");
    expect($final['exitCode'])->toBe(0);
});

test('python runs can be stopped while waiting for input and cannot be accessed by another session', function () {
    $id = $this->postJson(route('ide.api.python.start'), ['code' => "input('Waiting: ')"])->assertOk()->json('runId');
    $this->pythonRuns[] = $id;
    awaitPythonState($this, $id, fn ($s) => str_contains($s['stdout'], 'Waiting: '));

    $this->withSession(['python_owner' => 'someone-else']);
    $this->getJson(route('ide.api.python.status', ['runId' => $id]))->assertNotFound();
    $this->postJson(route('ide.api.python.input'), ['runId' => $id, 'input' => 'secret'])->assertNotFound();
    $this->postJson(route('ide.api.python.stop'), ['runId' => $id])->assertNotFound();

    $this->withSession(['python_owner' => explode(':', $this->pythonOwner, 2)[1]]);
    $this->postJson(route('ide.api.python.stop'), ['runId' => $id])->assertOk();
    $final = awaitPythonState($this, $id, fn ($s) => ! $s['running']);
    expect($final['exitCode'])->not->toBe(0);
    expect($final['stderr'])->toContain('Stopped.');
});

test('python reports tracebacks and stops excessive output', function () {
    foreach (["raise ValueError('example failure')", "while True: print('x' * 10000)"] as $code) {
        $id = $this->postJson(route('ide.api.python.start'), ['code' => $code])->assertOk()->json('runId');
        $this->pythonRuns[] = $id;
        $final = awaitPythonState($this, $id, fn ($s) => ! $s['running']);
        expect($final['exitCode'])->not->toBe(0);
        expect($final['stderr'])->toContain(str_starts_with($code, 'raise') ? 'ValueError: example failure' : 'Output limit reached');
    }
});
