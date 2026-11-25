<?php
// Fitur: Panel chat dan pengaturan model
// connect with ../../app/Models/User.php, ../../database/migrations/2026_10_18_100000_create_chat_storage_tables.php, ../../routes/web.php

use App\Models\User;
use Illuminate\Support\Facades\Http;

it('returns and stores the complete multi-file Senopati response through the IDE prompt route', function () {
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    $raw = "### [FILE: loader.py]\n```python\nprint('load')\n```\n### [FILE: tests.py]\n```python\nassert True\n```";
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => $raw]]]])]);
    $response = $this->actingAs(User::factory()->create())->postJson('/ide/api/agent/prompt', [
        'provider' => 'senopati', 'model' => 'qwen3:14b', 'apiKey' => 'fake-test-key',
        'prompt' => 'Create loader.py and tests.py',
    ]);
    $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('raw', $raw)->assertJsonCount(2, 'files');
    $this->assertDatabaseCount('chat_messages', 2);
});
