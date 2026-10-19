<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Services\AgentIde\FileExplorerService;
use App\Services\AgentIde\InteractivePythonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InteractivePythonController extends Controller
{
    public function __construct(private InteractivePythonService $python) {}

    public function start(Request $request, FileExplorerService $files): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string|max:200000']);
        $owner = $this->owner($request);
        if ($previous = $request->session()->get('python_run')) {
            try {
                $this->python->stop($previous, $owner);
            } catch (NotFoundHttpException) {
                // Previous output may already have expired.
            }
        }
        $run = $this->python->start($data['code'], $files->basePath(), $owner);
        $request->session()->put('python_run', $run['runId']);

        return response()->json(['success' => true] + $run);
    }

    public function status(Request $request): JsonResponse
    {
        $data = $request->validate(['runId' => 'required|string|size:48']);

        return response()->json(['success' => true] + $this->python->status($data['runId'], $this->owner($request)));
    }

    public function input(Request $request): JsonResponse
    {
        $data = $request->validate(['runId' => 'required|string|size:48', 'input' => 'present|nullable|string|max:8192']);
        $this->python->input($data['runId'], $this->owner($request), (string) $data['input']);

        return response()->json(['success' => true]);
    }

    public function stop(Request $request): JsonResponse
    {
        $data = $request->validate(['runId' => 'required|string|size:48']);
        $this->python->stop($data['runId'], $this->owner($request));

        return response()->json(['success' => true]);
    }

    private function owner(Request $request): string
    {
        return $this->python->owner($request);
    }
}
