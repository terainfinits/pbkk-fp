<?php

namespace App\Services\AgentIde;

use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class TerminalService
{
    public function createSession(Authenticatable $user): array
    {
        $userId = (string) $user->getAuthIdentifier();

        $workspacePath = $this->resolveWorkspacePath();

        $sessionId = Cache::lock("terminal-session-lock:{$userId}", 10)
            ->block(5, function () use ($userId) {
                return Cache::rememberForever(
                    "terminal-session:{$userId}",
                    fn () => (string) Str::uuid()
                );
            });

        $now = time();

        $payload = [
            'iss' => config('services.terminal.issuer'),
            'aud' => config('services.terminal.audience'),
            'sub' => $userId,
            'jti' => (string) Str::uuid(),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 60,
            'session_id' => $sessionId,
            'workspace_path' => $workspacePath,
        ];

        $ticket = JWT::encode(
            $payload,
            config('services.terminal.secret'),
            'HS256'
        );

        return [
            'session_id' => $sessionId,
            'ticket' => $ticket,
            'ws_url' => config('services.terminal.ws_url'),
            'expires_in' => 60,
        ];
    }

    private function resolveWorkspacePath(): string
    {
        $path = base_path('folder-generated');

        $realPath = realpath($path);

        if ($realPath === false || !is_dir($realPath)) {
            throw new RuntimeException(
                "Workspace directory not found: {$path}"
            );
        }

        return $realPath;
    }
}
