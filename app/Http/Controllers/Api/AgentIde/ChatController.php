<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\AgentIde\AgentContextBuilder;
use App\Services\AgentIde\AgentResponseFormatter;
use App\Services\AgentIde\FallbackAgentResponder;
use App\Services\AgentIde\LlmProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(
        private AgentContextBuilder $contextBuilder,
        private LlmProviderService $llm,
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

        // 1. Resolve or create chat session
        $session = null;
        if ($sessionId) {
            $session = ChatSession::find($sessionId);
        }

        if (!$session) {
            $title = Str::limit(trim($prompt), 35, '...');
            $session = ChatSession::create([
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

        try {
            $formatted = null;

            if (!empty($apiKey)) {
                $response = $this->llm->dispatch($provider, $model, $apiKey, $systemContext, $prompt, $history);

                if (!$response['success']) {
                    return response()->json(['success' => false, 'error' => $response['error'] ?? 'Live API call failed'], 400);
                }

                $formatted = $this->formatter->format($response['text'], $provider, $model, $targetFile, $targetDirectory, $prompt);
            } else {
                $formatted = $this->fallback->generate($prompt, $provider, $model, $targetFile, $targetDirectory);
            }

            // 3. Persist assistant response
            if (!empty($formatted['success'])) {
                $session->messages()->create([
                    'role' => 'assistant',
                    'content' => $formatted['explanation'] ?? '',
                    'metadata' => [
                        'raw' => $formatted['raw'] ?? '',
                        'code' => $formatted['code'] ?? null,
                        'language' => $formatted['language'] ?? null,
                        'targetPath' => $formatted['targetPath'] ?? null,
                        'steps' => $formatted['steps'] ?? [],
                    ],
                ]);
            }

            $formatted['sessionId'] = $session->id;
            $formatted['sessionTitle'] = $session->title;

            return response()->json($formatted);
        } catch (\Throwable $e) {
            Log::error('Agent prompt error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Agent processing exception: ' . $e->getMessage(),
            ], 500);
        }
    }
}
