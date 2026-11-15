<?php

namespace App\Services\AgentIde;

use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use RuntimeException;

class TerminalService
{
    public function createSession(Authenticatable $user): array
    {
        $workspacePath = $this->resolveWorkspacePath($user);

        if (!is_dir($workspacePath)) {
            throw new RuntimeException('Workspace not found.');
        }

        $sessionId = (string) Str::uuid();

        $now = time();

        $payload = [
            'iss' => config('services.terminal.issuer'),
            'aud' => config('services.terminal.audience'),
            'sub' => (string) $user->getAuthIdentifier(),
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

    private function resolveWorkspacePath(Authenticatable $user): string
    {
        $path = base_path('folder-generated/');

        if (!$path) {
            throw new RuntimeException('IDE_WORKSPACE_PATH is not configured.');
        }

        $realPath = realpath($path);

        if ($realPath === false || !is_dir($realPath)) {
            throw new RuntimeException('Workspace directory not found.');
        }

        return $realPath;
    }
}
