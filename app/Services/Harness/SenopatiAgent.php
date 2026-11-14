<?php

namespace App\Services\Harness;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SenopatiAgent
{
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
        try {
            $response = Http::withToken($key)->connectTimeout(min(10, $timeout))->timeout($timeout)
                ->withoutRedirecting()->post($endpoint, [
                    'model' => config('chat_ai.senopati_model'),
                    'messages' => $messages,
                    'stream' => false,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException) {
            throw new RuntimeException('Senopati tidak terhubung. Periksa endpoint dan akses jaringan ITS/VPN.');
        }
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
