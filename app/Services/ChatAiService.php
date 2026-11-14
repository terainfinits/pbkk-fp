<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ChatAiService
{
    public function reply(string $prompt): array
    {
        $messages = [
            ['role' => 'system', 'content' => config('chat_ai.system_prompt')],
            ['role' => 'user', 'content' => $prompt],
        ];
        $model = config('chat_ai.ollama_model');
        try {
            $response = Http::connectTimeout(3)->timeout(60)->post(rtrim(config('chat_ai.ollama_url'), '/').'/api/chat', [
                'model' => $model, 'messages' => $messages, 'stream' => false,
            ]);
        } catch (ConnectionException) {
            $response = null;
        }

        if ($response === null || $response->serverError()) {
            abort_unless(filled(config('services.senopati.api_key')), 503, 'LLM service unavailable.');
            $model = config('chat_ai.senopati_model');
            try {
                $response = Http::withToken(config('services.senopati.api_key'))
                    ->connectTimeout(5)->timeout(90)
                    ->post(rtrim(config('services.senopati.base_url'), '/').'/llm-gateway/v1/chat/completions', [
                        'model' => $model, 'messages' => $messages, 'stream' => false,
                    ]);
            } catch (ConnectionException) {
                abort(503, 'LLM service unavailable.');
            }
            $text = $response->json('choices.0.message.content');
            $tokens = $response->json('usage.total_tokens');
        } else {
            $text = $response->json('message.content');
            $tokens = $response->json('prompt_eval_count') !== null && $response->json('eval_count') !== null
                ? $response->json('prompt_eval_count') + $response->json('eval_count') : null;
        }

        abort_unless($response->successful() && is_string($text) && trim($text) !== '', 502, 'Invalid response from LLM provider.');

        return ['ai_response' => $text, 'model_used' => $response->json('model') ?: $model, 'tokens_consumed' => $tokens];
    }
}
