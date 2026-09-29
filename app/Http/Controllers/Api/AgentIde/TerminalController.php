<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Services\AgentIde\TerminalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    public function __construct(private TerminalService $terminal) {
    }

    /**
     * Execute a shell command and return the output.
     *
     * POST /ide/api/terminal/execute
     * Body: { command: string, cwd: string }
     */
    public function execute(Request $request): JsonResponse
    {
        $command = $request->input('command', '');
        $cwd     = $request->input('cwd') ?? '';

        if (empty(trim($command))) {
            return response()->json(['success' => false, 'error' => 'No command provided'], 400);
        }

        $result = $this->terminal->execute($command, $cwd);

        return response()->json([
            'success'         => true,
            'stdout'          => $result['stdout'] ?? '',
            'stderr'          => $result['stderr'] ?? '',
            'exitCode'        => $result['exitCode'] ?? -1,
            'executionTimeMs' => $result['executionTimeMs'] ?? 0,
            'cwd'             => $result['cwd'] ?? $cwd,
        ]);
    }
}
