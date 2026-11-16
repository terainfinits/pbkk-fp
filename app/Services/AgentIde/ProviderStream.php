<?php

namespace App\Services\AgentIde;

use Illuminate\Http\Client\PendingRequest;
use RuntimeException;

class ProviderStream
{
    public function read(PendingRequest $http, string $url, array $payload, string $format, callable $delta): array
    {
        if ($format === 'gemini') {
            $url = str_replace(':generateContent?', ':streamGenerateContent?alt=sse&', $url);
        } else {
            $payload['stream'] = true;
        }
        $response = $http->withOptions(['stream' => true, 'read_timeout' => 120])->post($url, $payload);
        if (! $response->successful()) {
            return ['success' => false, 'error' => $response->json('error.message') ?: 'Provider HTTP '.$response->status()];
        }

        $body = $response->toPsrResponse()->getBody();
        $text = $buffer = '';
        $finished = $limited = false;
        try {
            while (! $body->eof()) {
                $chunk = $body->read(4096);
                if ($chunk === '' && ! $body->eof()) {
                    throw new RuntimeException('Provider stream timed out.');
                }
                $buffer .= $chunk;
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);
                if ($body->eof() && $buffer !== '') {
                    $lines[] = $buffer;
                    $buffer = '';
                }
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($format !== 'ollama') {
                        if (! str_starts_with($line, 'data:')) {
                            continue;
                        }
                        $line = trim(substr($line, 5));
                    }
                    if ($line === '[DONE]') {
                        $finished = true;
                        continue;
                    }
                    if ($line === '') {
                        continue;
                    }
                    $frame = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
                    if (isset($frame['error'])) {
                        throw new RuntimeException('Provider interrupted the response.');
                    }
                    $reason = match ($format) {
                        'gemini' => $frame['candidates'][0]['finishReason'] ?? null,
                        'claude' => $frame['delta']['stop_reason'] ?? null,
                        'ollama' => $frame['done_reason'] ?? null,
                        default => $frame['choices'][0]['finish_reason'] ?? null,
                    };
                    $limited = $limited || in_array($reason, ['length', 'max_tokens', 'MAX_TOKENS'], true);
                    $finished = $finished || $reason !== null || ($frame['done'] ?? false) || ($frame['type'] ?? '') === 'message_stop';
                    $piece = match ($format) {
                        'gemini' => implode('', array_column(array_filter($frame['candidates'][0]['content']['parts'] ?? [], fn ($p) => empty($p['thought'])), 'text')),
                        'claude' => $frame['delta']['text'] ?? '',
                        'ollama' => $frame['message']['content'] ?? '',
                        default => $frame['choices'][0]['delta']['content'] ?? '',
                    };
                    if ($piece !== '') {
                        $text .= $piece;
                        $delta($piece);
                    }
                }
            }
        } finally {
            $body->close();
        }
        if (! $finished || $text === '') {
            throw new RuntimeException('Provider stream ended before a complete response arrived.');
        }

        return ['success' => true, 'text' => $text, 'truncated' => $limited];
    }
}
