<?php

use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
});

test('guest sees auth pages and cannot access the ide or its api', function () {
    $this->get('/')->assertRedirect(route('login'));
    $this->get(route('login'))->assertOk()->assertSee('Auth/Login');
    $this->get(route('register'))->assertOk()->assertSee('Auth/Register');
    $this->get(route('ide.index'))->assertRedirect(route('login'));
    $this->getJson(route('ide.api.tree'))->assertUnauthorized();
    $this->postJson(route('ide.api.file.save'), [])->assertUnauthorized();
    $this->get('/agent')->assertNotFound();
    $this->get('/mahasiswa/5025241104')->assertNotFound();
    $this->get('/hitung-ipk')->assertNotFound();
    $this->get('/dashboard')->assertNotFound();
});

test('registration signs in and redirects to the ide', function () {
    $response = $this->post(route('register'), [
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('ide.index'));
    $this->assertAuthenticated();
    expect(Hash::check('Password123!', User::where('email', 'tester@example.com')->firstOrFail()->password))->toBeTrue();
});

test('login rejects wrong credentials and logout ends the session', function () {
    $user = User::factory()->create(['password' => 'Password123!']);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'Password123!'])
        ->assertRedirect(route('ide.index'));
    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('chat sessions are only accessible to their owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $session = ChatSession::create(['user_id' => $owner->id, 'title' => 'Private chat']);

    $this->actingAs($other);
    $this->getJson(route('ide.api.chats.index'))->assertJsonCount(0, 'sessions');
    $this->getJson(route('ide.api.chats.show', $session->id))->assertNotFound();
    $this->get(route('ide.api.chats.export', $session->id))->assertNotFound();
    $this->putJson(route('ide.api.chats.update', $session->id), ['title' => 'Changed'])->assertNotFound();
    $this->postJson(route('ide.api.agent.prompt'), ['sessionId' => $session->id, 'prompt' => 'Hello'])->assertNotFound();
    $this->deleteJson(route('ide.api.chats.destroy', $session->id))->assertNotFound();

    expect($session->fresh()->title)->toBe('Private chat');
});
