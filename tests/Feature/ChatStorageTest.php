<?php

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can create, list, and view chat sessions', function () {
    // 1. Create a session
    $createRes = $this->postJson(route('ide.api.chats.store'), [
        'title' => 'Diskusi Laravel',
        'provider' => 'gemini',
        'model' => 'gemini-3.6-flash',
    ]);

    $createRes->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('session.title', 'Diskusi Laravel');

    $sessionId = $createRes->json('session.id');

    // 2. List sessions
    $listRes = $this->getJson(route('ide.api.chats.index'));
    $listRes->assertStatus(200)
        ->assertJsonPath('success', true);

    expect(collect($listRes->json('sessions'))->pluck('id'))->toContain($sessionId);

    // 3. View session detail
    $showRes = $this->getJson(route('ide.api.chats.show', ['id' => $sessionId]));
    $showRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('session.id', $sessionId);
});

test('agent prompt stores user and assistant messages in database', function () {
    $response = $this->postJson(route('ide.api.agent.prompt'), [
        'prompt' => 'Tolong buatkan fungsi kalkulasi matematika',
        'provider' => 'gemini',
        'model' => 'gemini-3.6-flash',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $sessionId = $response->json('sessionId');
    expect($sessionId)->not->toBeNull();

    $session = ChatSession::with('messages')->find($sessionId);
    expect($session)->not->toBeNull();
    expect($session->messages->count())->toBeGreaterThanOrEqual(2);

    $roles = $session->messages->pluck('role')->toArray();
    expect($roles)->toContain('user');
    expect($roles)->toContain('assistant');
});

test('can delete a chat session and cascade delete its messages', function () {
    $session = ChatSession::create(['title' => 'Sesi untuk dihapus', 'user_id' => $this->user->id]);
    $session->messages()->create(['role' => 'user', 'content' => 'Pesan sementara']);

    $deleteRes = $this->deleteJson(route('ide.api.chats.destroy', ['id' => $session->id]));
    $deleteRes->assertStatus(200)
        ->assertJsonPath('success', true);

    expect(ChatSession::find($session->id))->toBeNull();
    expect(ChatMessage::where('chat_session_id', $session->id)->count())->toBe(0);
});

test('can search and filter chat sessions by keyword in title or message content', function () {
    $session1 = ChatSession::create(['title' => 'Implementasi Algoritma Dijkstra', 'user_id' => $this->user->id]);
    $session1->messages()->create(['role' => 'user', 'content' => 'Bagaimana cara kerja graf berbobot?']);

    $session2 = ChatSession::create(['title' => 'Setup Docker Compose', 'user_id' => $this->user->id]);
    $session2->messages()->create(['role' => 'user', 'content' => 'Buatkan konfigurasi docker mysql']);

    // Search by title keyword
    $resTitle = $this->getJson(route('ide.api.chats.index', ['search' => 'Dijkstra']));
    $resTitle->assertStatus(200);
    $idsTitle = collect($resTitle->json('sessions'))->pluck('id');
    expect($idsTitle)->toContain($session1->id);
    expect($idsTitle)->not->toContain($session2->id);

    // Search by message content keyword
    $resMsg = $this->getJson(route('ide.api.chats.index', ['search' => 'graf berbobot']));
    $resMsg->assertStatus(200);
    $idsMsg = collect($resMsg->json('sessions'))->pluck('id');
    expect($idsMsg)->toContain($session1->id);
    expect($idsMsg)->not->toContain($session2->id);
});

test('can export chat session as markdown document', function () {
    $session = ChatSession::create([
        'user_id' => $this->user->id,
        'title' => 'Percakapan Ekspor',
        'provider' => 'gemini',
        'model' => 'gemini-3.6-flash',
    ]);
    $session->messages()->create([
        'role' => 'user',
        'content' => 'Tuliskan kode python fibonacci',
    ]);
    $session->messages()->create([
        'role' => 'assistant',
        'content' => 'Berikut implementasi kode fibonacci:',
        'metadata' => [
            'language' => 'python',
            'code' => 'def fib(n): return n if n <= 1 else fib(n-1) + fib(n-2)',
            'targetPath' => 'fib.py',
            'steps' => [['title' => 'Synthesis', 'detail' => 'Formulating fibonacci function']],
        ],
    ]);

    $res = $this->get(route('ide.api.chats.export', ['id' => $session->id]));
    $res->assertStatus(200);
    $res->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');

    $content = $res->getContent();
    expect($content)->toContain('# Percakapan Ekspor');
    expect($content)->toContain('### 👤 User');
    expect($content)->toContain('Tuliskan kode python fibonacci');
    expect($content)->toContain('def fib(n):');
    expect($content)->toContain('Agent Reasoning Steps');
});
