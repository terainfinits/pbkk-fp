<?php

test('ide page loads successfully', function () {
    $response = $this->get(route('ide.index'));
    $response->assertStatus(200);
    $response->assertSee('Antigravity');
});

test('kernels api detects python php node', function () {
    $response = $this->getJson(route('ide.api.kernels'));
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'kernels']);
});

test('code runner executes python code in kernel', function () {
    $response = $this->postJson(route('ide.api.code.run'), [
        'code' => 'print("Antigravity Python Kernel Test")',
        'language' => 'python'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'kernel', 'stdout', 'exitCode', 'executionTimeMs'])
        ->assertJsonPath('exitCode', 0);

    expect($response->json('stdout'))->toContain('Antigravity Python Kernel Test');
});

test('code runner executes php code in kernel', function () {
    $response = $this->postJson(route('ide.api.code.run'), [
        'code' => '<?php echo "Antigravity PHP Kernel Test";',
        'language' => 'php'
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('exitCode', 0);

    expect($response->json('stdout'))->toContain('Antigravity PHP Kernel Test');
});
