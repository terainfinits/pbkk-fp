<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Services\AgentIde\FileExplorerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(private FileExplorerService $files) {}

    public function tree(Request $request): JsonResponse
    {
        $request->validate(['directory' => 'nullable|string|max:1024']);
        $result = $this->files->scanTree($request->input('directory') ?? '');

        return response()->json(['success' => true] + $result);
    }

    public function read(Request $request): JsonResponse
    {
        $request->validate(['path' => 'required|string|max:1024']);
        $path = $request->input('path');
        if (! $path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $result = $this->files->readFile($path);

        return response()->json(['success' => true] + $result);
    }

    public function save(Request $request): JsonResponse
    {
        $request->validate(['path' => 'required|string|max:1024', 'content' => 'nullable|string|max:1000000']);
        $path = $request->input('path');
        if (! $path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $result = $this->files->saveFile($path, $request->input('content') ?? '');

        return response()->json(['success' => true, 'message' => 'File saved successfully'] + $result);
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate(['path' => 'required|string|max:1024', 'type' => 'required|in:file,directory', 'content' => 'nullable|string|max:1000000']);
        $path = $request->input('path');
        $type = $request->input('type', 'file');

        if (! $path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $result = $this->files->createItem($path, $type, $request->input('content') ?? '');

        return response()->json([
            'success' => true,
            'message' => ucfirst($type).' created successfully',
            'path' => $result['path'],
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['path' => 'required|string|max:1024']);
        $path = $request->input('path');
        if (! $path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $deletedPath = $this->files->deleteItem($path);

        return response()->json(['success' => true, 'message' => 'Deleted successfully', 'path' => $deletedPath]);
    }
}
