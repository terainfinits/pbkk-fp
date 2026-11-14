<?php

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Http::preventStrayRequests();
    config(['chat_ai.ollama_url' => 'http://localhost:11434']);
});

it('rejects requests without a token before contacting the provider', function (): void {
    $this->postJson('/api/chat-ai', ['prompt' => 'Hello'])->assertUnauthorized();
    Http::assertNothingSent();
});

it('rejects invalid bearer tokens', function (): void {
    $this->withToken('invalid')->postJson('/api/chat-ai', ['prompt' => 'Hello'])->assertUnauthorized();
});

it('validates the prompt before contacting the provider', function ($prompt): void {
    $token = User::factory()->create()->createToken('test')->plainTextToken;
    $this->withToken($token)->postJson('/api/chat-ai', ['prompt' => $prompt])
        ->assertUnprocessable()->assertJsonValidationErrors('prompt');
    Http::assertNothingSent();
})->with([
    'null' => [null],
    'empty string' => [''],
    'array instead of string' => [['invalid']],
    'over 1000 characters' => [str_repeat('a', 1001)],
]);

it('returns the contract and records the authenticated user and real usage', function (): void {
    $user = User::factory()->create();
    Http::fake(['localhost:11434/*' => Http::response([
        'model' => 'qwen3:14b', 'message' => ['content' => 'Middleware adalah...'],
        'prompt_eval_count' => 8, 'eval_count' => 12,
    ])]);
    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/chat-ai', ['prompt' => 'Jelaskan middleware!'])
        ->assertOk()->assertJsonPath('status', 'success')
        ->assertJsonPath('data.ai_response', 'Middleware adalah...')
        ->assertJsonStructure(['data' => ['session_id', 'model_used', 'prompt', 'ai_response', 'timestamp']]);
    $this->assertDatabaseHas('ai_chat_logs', ['user_id' => $user->id, 'tokens_consumed' => 20]);
    Http::assertSent(fn ($request) => $request['messages'][0]['role'] === 'system');
});

it('falls back to Senopati when Ollama is offline', function (): void {
    config(['services.senopati.api_key' => 'fake-secret', 'services.senopati.base_url' => 'https://senopati.test']);
    Http::fake([
        'localhost:11434/*' => fn () => throw new ConnectionException('offline'),
        'senopati.test/*' => Http::response(['model' => 'fallback-model', 'choices' => [['message' => ['content' => 'Jawaban']]], 'usage' => ['total_tokens' => 42]]),
    ]);
    $user = User::factory()->create();
    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/chat-ai', ['prompt' => 'Hello'])
        ->assertOk()->assertJsonPath('data.model_used', 'fallback-model');
    $this->assertDatabaseHas('ai_chat_logs', ['tokens_consumed' => 42]);
});

it('does not log successful chats when both providers fail', function (): void {
    config(['services.senopati.api_key' => null]);
    Http::fake(['localhost:11434/*' => Http::response([], 503)]);
    $user = User::factory()->create();
    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/chat-ai', ['prompt' => 'Hello'])->assertStatus(503);
    $this->assertDatabaseCount('ai_chat_logs', 0);
});
