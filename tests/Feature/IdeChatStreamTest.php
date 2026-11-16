<?php

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function providerFrame(string $content, ?string $reason): string
{
    return 'data: '.json_encode(['choices' => [['delta' => ['content' => $content], 'finish_reason' => $reason]]])."\r\n\r\n";
}

function streamIdeChat(int $limit): string
{
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    return test()->actingAs(User::factory()->create())
        ->postJson('/ide/api/agent/prompt', ['prompt' => 'Write two files', 'apiKey' => 'test', 'provider' => 'senopati', 'maxIterations' => $limit],
            ['Accept' => 'text/event-stream, application/json;q=0.9'])
        ->assertOk()->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8')->streamedContent();
}

it('streams deltas, resumes a truncated file and stores both files once', function () {
    Http::preventStrayRequests();
    Http::fakeSequence()
        ->push(providerFrame("### [FILE: a.py]\n~~~py\nprint(", 'length'))
        ->push(providerFrame("1)\n~~~\n[TASK_CONTINUE]", 'stop'))
        ->push(providerFrame("## b.py\n~~~py\nprint(2)\n~~~\n[TASK_DONE]", 'stop'));
    $stream = streamIdeChat(4);
    expect(substr_count($stream, 'event: iteration'))->toBe(3)
        ->and(substr_count($stream, 'event: delta'))->toBe(3)
        ->and(substr_count($stream, 'event: done'))->toBe(1);
    $saved = ChatMessage::where('role', 'assistant')->sole()->metadata;
    expect($saved['stopReason'])->toBe('completed')
        ->and(array_column($saved['files'], 'path'))->toBe(['a.py', 'b.py'])
        ->and($saved['files'][0]['code'])->toBe('print(1)')
        ->and($saved['raw'])->not->toContain('[TASK_');
});

it('reports the iteration limit and leaves unfinished fences out of review', function () {
    Http::fakeSequence()->push(providerFrame("### [FILE: a.py]\n~~~py\nx", 'length'));
    expect(streamIdeChat(1))->toContain('event: done');
    $saved = ChatMessage::where('role', 'assistant')->sole()->metadata;
    expect($saved['stopReason'])->toBe('iteration_limit')->and($saved['files'])->toBe([]);
});

it('reports an interrupted provider without persisting a completed answer', function () {
    Http::fakeSequence()->push(providerFrame('partial', null));
    expect(streamIdeChat(2))->toContain('event: error')->not->toContain('event: done');
    expect(ChatMessage::where('role', 'assistant')->count())->toBe(0);
});
