<?php
// Fitur: Harness tugas coding
// connect with ../../../config/chat_ai.php, ../../../config/services.php, ../../Jobs/RunCodingAgent.php

namespace App\Services\Harness;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SenopatiAgent
{
    private ?\Closure $recordBody = null;

    public function recordBodies(\Closure $record): void
    {
        $this->recordBody = $record;
    }

    public function next(array $messages, int $timeout): string
    {
        $key = config('services.senopati.api_key');
        if (!is_string($key) || trim($key) === '') {
            throw new RuntimeException('Isi SENOPATI_API_KEY pada environment server.');
        }
        $endpoint = config('services.senopati.endpoint')
            ?: rtrim(config('services.senopati.base_url'), '/').'/llm-gateway/v1/chat/completions';
        if (parse_url($endpoint, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('SENOPATI_ENDPOINT harus memakai HTTPS.');
        }
        $payload = [
            'model' => config('chat_ai.senopati_model'),
            'messages' => $messages,
            'stream' => false,
            'temperature' => 0,
            'response_format' => ['type' => 'json_object'],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($this->recordBody) ($this->recordBody)('request', $body, null);
        try {
            $response = Http::withToken($key)->connectTimeout(min(10, $timeout))->timeout($timeout)
                ->withoutRedirecting()->withBody($body, 'application/json')->post($endpoint);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException(\App\Services\SenopatiConnection::describe($e));
        }
        if ($this->recordBody) ($this->recordBody)('response', $response->body(), $response->status());
        if (!$response->successful()) {
            throw new RuntimeException('Senopati mengembalikan HTTP '.$response->status().'. Periksa akses API dan model.');
        }
        $text = $response->json('choices.0.message.content');
        if (!is_string($text) || trim($text) === '') {
            throw new RuntimeException('Senopati mengembalikan jawaban kosong atau format yang tidak sesuai.');
        }

        return $text;
    }
}
