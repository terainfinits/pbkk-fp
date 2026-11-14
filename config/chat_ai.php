<?php

return [
    'ollama_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
    'ollama_model' => env('OLLAMA_MODEL', 'qwen3:14b'),
    'senopati_model' => env('SENOPATI_MODEL', 'qwen3:14b'),
    'system_prompt' => env('CHAT_AI_SYSTEM_PROMPT', 'Anda adalah asisten kode ITS. Bantu menulis dan meninjau kode, jelaskan bug dan rekomendasi keamanan secara ringkas dalam bahasa Indonesia.'),
];
