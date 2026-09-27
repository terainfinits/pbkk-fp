<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Services\AgentIde\FileExplorerService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(private FileExplorerService $files)
    {
    }

    public function tree(Request $request)
    {
        $result = $this->files->scanTree($request->input('directory', ''));

        return response()->json(['success' => true] + $result);
    }

    public function read(Request $request)
    {
        $path = $request->input('path');
        if (!$path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $result = $this->files->readFile($path);
        if (!$result) {
            return response()->json(['success' => false, 'error' => 'File not found or access denied'], 404);
        }

        return response()->json(['success' => true] + $result);
    }

    public function save(Request $request)
    {
        $path = $request->input('path');
        if (!$path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $result = $this->files->saveFile($path, $request->input('content', ''));

        return response()->json(['success' => true, 'message' => 'File saved successfully'] + $result);
    }

    public function create(Request $request)
    {
        $path = $request->input('path');
        $type = $request->input('type', 'file');

        if (!$path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $result = $this->files->createItem($path, $type, $request->input('content', ''));

        if (!$result['ok']) {
            return response()->json(['success' => false, 'error' => $result['error']], 409);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($type) . ' created successfully',
            'path' => $result['path'],
        ]);
    }

    public function delete(Request $request)
    {
        $path = $request->input('path');
        if (!$path) {
            return response()->json(['success' => false, 'error' => 'Path is required'], 400);
        }

        $deletedPath = $this->files->deleteItem($path);
        if ($deletedPath === null) {
            return response()->json(['success' => false, 'error' => 'Item not found or access denied'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Deleted successfully', 'path' => $deletedPath]);
    }
}
