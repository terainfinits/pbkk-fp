<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Services\AgentIde\AgentContextBuilder;
use App\Services\AgentIde\AgentResponseFormatter;
use App\Services\AgentIde\FallbackAgentResponder;
use App\Services\AgentIde\ChatAgentLoop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(
        private AgentContextBuilder $contextBuilder,
        private ChatAgentLoop $loop,
        private AgentResponseFormatter $formatter,
        private FallbackAgentResponder $fallback,
    ) {
    }

    /**
     * Connect to the chosen LLM provider (or fall back to the built-in
     * canned engine when no API key is present) and return a structured
     * agentic response for the chatbot UI, storing conversation in database.
     */
    public function prompt(Request $request)
    {
        // PHP on Windows counts time spent waiting for the provider as execution time.
        set_time_limit(150);
        $provider = $request->input('provider', 'ollama_cloud');
        $model = $request->input('model', 'kimi-k2.7-code');
        $prompt = $request->input('prompt', '');
        $apiKey = $request->input('apiKey', '');
        $targetDirectory = $request->input('targetDirectory', '');
        $targetFile = $request->input('targetFile', '');
        $currentCode = $request->input('currentCode', '');
        $history = $request->input('history', []);
        $sessionId = $request->input('sessionId') ?: $request->input('session_id');

        if (empty(trim($prompt))) {
            return response()->json(['success' => false, 'error' => 'Prompt cannot be empty'], 400);
        }

        $limit = filter_var($request->input('maxIterations', 4), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 12],
        ]);
        if ($limit === false) {
            return response()->json(['success' => false, 'error' => 'maxIterations must be between 1 and 12.'], 422);
        }

        // default API key for Senopati (env config only, no hardcoded secrets)
        if (empty($apiKey) && $provider === 'senopati') {
            $apiKey = config('services.senopati.api_key', '');
        }

        // 1. Resolve or create chat session
        $session = null;
        if ($sessionId) {
            $session = ChatSession::where('user_id', $request->user()->id)->find($sessionId);
        }

        if ($sessionId && !$session) {
            return response()->json(['success' => false, 'error' => 'Chat session not found.'], 404);
        }

        if (!$session) {
            $title = Str::limit(trim($prompt), 35, '...');
            $session = ChatSession::create([
                'user_id' => $request->user()->id,
                'title' => $title,
                'provider' => $provider,
                'model' => $model,
            ]);
        } else {
            $session->update([
                'provider' => $provider,
                'model' => $model,
            ]);
            $session->touch();
        }

        // 2. Persist user message
        $session->messages()->create([
            'role' => 'user',
            'content' => $prompt,
        ]);

        $systemContext = $this->contextBuilder->build($targetDirectory, $targetFile, $currentCode);

        $run = function (?callable $emit) use ($provider, $model, $apiKey, $systemContext, $prompt, $history, $limit, $targetFile, $targetDirectory, $session) {
            $formatted = null;

            if (!empty($apiKey)) {
                $response = $this->loop->run($provider, $model, $apiKey, $systemContext, $prompt, $history, $limit, $emit);

                if (!$response['success']) {
                    // network/DNS failures are environmental (e.g. Senopati
                    // only resolves inside the ITS campus network). Serve the
                    // built-in generator so the user still gets an answer, and
                    // flag it in the UI. Auth/invalid-key errors keep failing
                    // loudly — we do NOT fall back for those (decision #5).
                    if (empty($response['network_error'])) {
                        return $response;
                    }

                    $notice = $response['error'] . ' Response came from the built-in fallback generator, not the live AI.';
                    $formatted = $this->fallback->generate($prompt, $provider, $model, $targetFile, $targetDirectory, $notice);
                    $formatted['fallbackNotice'] = $notice;
                } else {
                    $formatted = $this->formatter->format($response['text'], $provider, $model, $targetFile, $targetDirectory, $prompt);
                }
            } else {
                $formatted = $this->fallback->generate($prompt, $provider, $model, $targetFile, $targetDirectory);
                $formatted['fallbackNotice'] = 'API key belum diatur. Respons ini berasal dari template bawaan.';
            }

            $formatted['stopReason'] = $response['stopReason'] ?? 'fallback';
            $formatted['iterations'] = $response['iterations'] ?? 0;
            $formatted['files'] ??= [['path' => $formatted['targetPath'], 'code' => $formatted['code'], 'language' => $formatted['language']]];
            // 3. Persist assistant response
            if (!empty($formatted['success'])) {
                $session->messages()->create([
                    'role' => 'assistant',
                    'content' => $formatted['explanation'] ?? '',
                    'metadata' => [
                        'raw' => $formatted['raw'] ?? '',
                        'stopReason' => $formatted['stopReason'],
                        'iterations' => $formatted['iterations'],
                        'fallbackNotice' => $formatted['fallbackNotice'] ?? null,
                        'files' => $formatted['files'] ?? [],
                        'code' => $formatted['code'] ?? null,
                        'language' => $formatted['language'] ?? null,
                        'targetPath' => $formatted['targetPath'] ?? null,
                        'steps' => $formatted['steps'] ?? [],
                    ],
                ]);
            }

            $formatted['sessionId'] = $session->id;
            $formatted['sessionTitle'] = $session->title;

            return $formatted;
        };

        if ($request->prefers(['application/json', 'text/event-stream']) === 'text/event-stream') {
            return response()->stream(function () use ($run, $session) {
                $emit = static function (string $event, array $data) {
                    echo "event: {$event}\ndata: ".json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)."\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                };
                try {
                    $emit('session', ['sessionId' => $session->id]);
                    $result = $run($emit);
                    $emit($result['success'] ? 'done' : 'error', $result);
                } catch (\Throwable $e) {
                    report($e);
                    $emit('error', ['error' => 'Chat stream interrupted.']);
                }
            }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache, no-transform', 'X-Accel-Buffering' => 'no']);
        }

        try {
            $formatted = $run(null);
            return response()->json($formatted, $formatted['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            Log::error('Agent prompt error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Agent processing exception: ' . $e->getMessage(),
            ], 500);
        }
    }
}
