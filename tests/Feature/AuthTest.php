<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can see login and registration and must sign in to use the IDE', function () {
    $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/Login'));
    $this->get('/register')->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/Register'));
    $this->get('/')->assertRedirect('/login');
    $this->getJson('/ide/api/tree')->assertUnauthorized();
    foreach (['file/read', 'file/save', 'file/create', 'file/delete', 'code/run', 'code/python/start', 'code/python/input', 'code/python/stop', 'terminal/execute', 'agent/prompt'] as $endpoint) {
        $this->postJson('/ide/api/'.$endpoint)->assertUnauthorized();
    }
    $this->getJson('/ide/api/code/python/status')->assertUnauthorized();
    $this->getJson('/ide/api/kernels')->assertUnauthorized();
});

test('registration persists a hashed password and signs the user in', function () {
    $this->post('/register', ['name' => 'Ada', 'email' => 'ada@example.test', 'password' => 'test-password', 'password_confirmation' => 'test-password'])->assertRedirect('/');
    $user = User::where('email', 'ada@example.test')->firstOrFail();
    expect(Hash::check('test-password', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
    $this->get('/login')->assertRedirect('/');
    $this->get('/register')->assertRedirect('/');
});

test('registration validates duplicate email password length and confirmation', function () {
    User::factory()->create(['email' => 'ada@example.test']);
    $this->post('/register', ['name' => '', 'email' => 'ada@example.test', 'password' => 'short', 'password_confirmation' => 'different'])
        ->assertSessionHasErrors(['name', 'email', 'password']);
    $this->assertGuest();
});

test('login verifies credentials and logout invalidates the session', function () {
    $user = User::factory()->create(['password' => 'test-password']);
    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->post('/login', ['email' => $user->email, 'password' => 'test-password'])->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
    $this->withSession(['private_state' => 'secret'])->post('/logout')->assertRedirect('/login')->assertSessionMissing('private_state');
    $this->assertGuest();
    $this->getJson('/ide/api/tree')->assertUnauthorized();
});

test('login and registration are rate limited', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => 'none@example.test', 'password' => 'incorrect']);
    }
    $this->post('/login', ['email' => 'none@example.test', 'password' => 'incorrect'])->assertTooManyRequests();
    foreach (range(1, 5) as $attempt) {
        $this->post('/register', []);
    }
    $this->post('/register', [])->assertTooManyRequests();
});

test('removed pages return a Vue 404 screen', function () {
    foreach (['/home', '/mahasiswa/5025241104', '/agent', '/hitung-ipk', '/dashboard', '/missing'] as $path) {
        $this->get($path)->assertNotFound()->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 404));
    }
});
