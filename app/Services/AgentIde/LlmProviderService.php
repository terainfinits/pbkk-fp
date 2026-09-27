<?php

namespace App\Services\AgentIde;

use Illuminate\Support\Facades\Http;

/**
 * Dispatches a prompt to whichever live LLM provider the user picked.
 * Each provider gets its own small, easy-to-audit method instead of one
 * giant switch statement full of inline HTTP calls.
 */
class LlmProviderService
{
    /**
     * @return array{success: bool, text?: string, error?: string}
     */
    public function dispatch(string $provider, string $model, string $apiKey, string $systemContext, string $prompt, array $history = []): array
    {
        return match (strtolower($provider)) {
            'gemini' => $this->callGemini($model, $apiKey, $systemContext, $prompt),
            'claude' => $this->callClaude($model, $apiKey, $systemContext, $prompt),
            'moonshot', 'kimi' => $this->callMoonshot($model, $apiKey, $systemContext, $prompt),
            'gpt', 'openai', 'deepseek' => $this->callOpenAiCompatible($provider, $model, $apiKey, $systemContext, $prompt),
            'ollama', 'ollama_cloud', 'ollama-cloud', 'ollamacloud' => $this->callOllama($model, $apiKey, $systemContext, $prompt),
            default => ['success' => false, 'error' => "Unknown provider: {$provider}"],
        };
    }

    private function callGemini(string $model, string $apiKey, string $systemContext, string $prompt): array
    {
        $effectiveModel = $model ?: 'gemini-3.6-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$effectiveModel}:generateContent?key={$apiKey}";

        $response = Http::timeout(60)->post($url, [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $systemContext . "\n\nUser Request: " . $prompt]],
            ]],
        ]);

        if ($response->successful()) {
            $text = $response->json('candidates.0.content.parts.0.text', '');
            return $text !== ''
                ? ['success' => true, 'text' => $text]
                : ['success' => false, 'error' => 'Gemini API returned an empty text response.'];
        }

        return ['success' => false, 'error' => "Gemini API Error ({$response->status()}): " . $this->extractError($response)];
    }

    private function callClaude(string $model, string $apiKey, string $systemContext, string $prompt): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model ?: 'claude-3-5-sonnet-20241022',
            'max_tokens' => 4096,
            'system' => $systemContext,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]);

        if ($response->successful()) {
            return ['success' => true, 'text' => $response->json('content.0.text', '')];
        }

        return ['success' => false, 'error' => "Claude API Error ({$response->status()}): " . $this->extractError($response)];
    }

    private function callMoonshot(string $model, string $apiKey, string $systemContext, string $prompt): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.moonshot.cn/v1/chat/completions', [
            'model' => $model ?: 'moonshot-v1-8k',
            'messages' => [
                ['role' => 'system', 'content' => $systemContext],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
        ]);

        if ($response->successful()) {
            return ['success' => true, 'text' => $response->json('choices.0.message.content', '')];
        }

        return ['success' => false, 'error' => "Kimi API Error ({$response->status()}): " . $this->extractError($response)];
    }

    private function callOpenAiCompatible(string $provider, string $model, string $apiKey, string $systemContext, string $prompt): array
    {
        $baseUrl = $provider === 'deepseek'
            ? 'https://api.deepseek.com/v1/chat/completions'
            : 'https://api.openai.com/v1/chat/completions';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($baseUrl, [
            'model' => $model ?: 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => $systemContext],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
        ]);

        if ($response->successful()) {
            return ['success' => true, 'text' => $response->json('choices.0.message.content', '')];
        }

        return ['success' => false, 'error' => ucfirst($provider) . " API Error ({$response->status()}): " . $this->extractError($response)];
    }

    private function callOllama(string $model, string $apiKey, string $systemContext, string $prompt): array
    {
        $effectiveModel = $model ?: 'kimi-k2.6';
        $messages = [
            ['role' => 'system', 'content' => $systemContext],
            ['role' => 'user', 'content' => $prompt],
        ];

        if (!empty($apiKey)) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post('https://ollama.com/api/chat', [
                'model' => $effectiveModel,
                'messages' => $messages,
                'stream' => false,
            ]);

            if ($response->successful()) {
                $text = $response->json('message.content') ?? $response->json('response') ?? $response->json('choices.0.message.content') ?? '';
                return $text !== ''
                    ? ['success' => true, 'text' => $text]
                    : ['success' => false, 'error' => 'Ollama Cloud API returned an empty text response.'];
            }

            return ['success' => false, 'error' => "Ollama Cloud API Error ({$response->status()}): " . $this->extractError($response)];
        }

        // No API key: fall back to a local Ollama instance if one is running.
        $localResponse = Http::timeout(60)->post('http://localhost:11434/api/chat', [
            'model' => $effectiveModel,
            'messages' => $messages,
            'stream' => false,
        ]);

        if ($localResponse->successful()) {
            $text = $localResponse->json('message.content') ?? $localResponse->json('response') ?? '';
            if ($text !== '') {
                return ['success' => true, 'text' => $text];
            }
        }

        return ['success' => false, 'error' => 'Ollama API key is missing for Ollama Cloud, and local Ollama server is not running on localhost:11434.'];
    }

    private function extractError($response): string
    {
        $json = $response->json();
        return $json['error']['message'] ?? $json['error'] ?? $response->body();
    }
}
