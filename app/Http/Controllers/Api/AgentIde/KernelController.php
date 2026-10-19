<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Services\AgentIde\FileExplorerService;
use App\Services\AgentIde\KernelRunnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KernelController extends Controller
{
    public function __construct(
        private KernelRunnerService $kernels,
        private FileExplorerService $files,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'kernels' => $this->kernels->detectKernels(),
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        $request->validate(['code' => 'nullable|string|max:200000', 'language' => 'nullable|string|max:30', 'path' => 'nullable|string|max:1024']);
        $result = $this->kernels->run(
            $request->input('code') ?? '',
            strtolower($request->input('language') ?? ''),
            $request->input('path') ?? '',
            $this->files->basePath(),
        );

        if (! $result['ok']) {
            return response()->json(['success' => false, 'error' => $result['error']], 400);
        }

        unset($result['ok']);

        return response()->json(['success' => true] + $result);
    }
}
