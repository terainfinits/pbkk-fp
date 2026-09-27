<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Services\AgentIde\AgentContextBuilder;
use App\Services\AgentIde\AgentResponseFormatter;
use App\Services\AgentIde\FallbackAgentResponder;
use App\Services\AgentIde\LlmProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * agentic response for the chatbot UI.
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

        if (empty(trim($prompt))) {
            return response()->json(['success' => false, 'error' => 'Prompt cannot be empty'], 400);
        }

        $systemContext = $this->contextBuilder->build($targetDirectory, $targetFile, $currentCode);

        try {
            if (!empty($apiKey)) {
                $response = $this->llm->dispatch($provider, $model, $apiKey, $systemContext, $prompt, $history);

                if (!$response['success']) {
                    return response()->json(['success' => false, 'error' => $response['error'] ?? 'Live API call failed'], 400);
                }

                return response()->json(
                    $this->formatter->format($response['text'], $provider, $model, $targetFile, $targetDirectory, $prompt)
                );
            }

            return response()->json(
                $this->fallback->generate($prompt, $provider, $model, $targetFile, $targetDirectory)
            );
        } catch (\Throwable $e) {
            Log::error('Agent prompt error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Agent processing exception: ' . $e->getMessage(),
            ], 500);
        }
    }
}
