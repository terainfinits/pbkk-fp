<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Services\AgentIde\TerminalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    public function __construct(
        private readonly TerminalService $terminalService
    ) {}

    public function createSession(Request $request): JsonResponse
    {
        $session = $this->terminalService->createSession(
            $request->user()
        );

        return response()->json($session);
    }
}
