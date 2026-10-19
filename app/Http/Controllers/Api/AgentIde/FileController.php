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

        $result = $this->files->createItem($path, $type, $request->input('content') ?? '');

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

    /**
     * Rename an existing file or directory. Only the final name changes;
     * the parent directory stays the same.
     *
     * POST /ide/api/file/rename
     * Body: { path: string, newName: string }
     */
    public function rename(Request $request)
    {
        $path = $request->input('path');
        $newName = $request->input('newName');

        if (!$path || !$newName) {
            return response()->json(['success' => false, 'error' => 'Path and new name are required'], 400);
        }

        $newPath = $this->files->renameItem($path, $newName);
        if ($newPath === null) {
            return response()->json(['success' => false, 'error' => 'Rename failed. Check name validity, duplicates, or permissions.'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Renamed successfully',
            'oldPath' => $path,
            'newPath' => $newPath,
        ]);
    }

    /**
     * Upload a single file or a .zip archive into the workspace.
     *
     * POST /ide/api/file/upload  (multipart/form-data)
     * Body: { upload: File, targetDirectory: string }
     */
    public function upload(Request $request)
    {
        $file = $request->file('upload');
        $targetDirectory = $request->input('targetDirectory', '');

        $result = $this->files->uploadFile($file, $targetDirectory);

        if (!$result['ok']) {
            return response()->json(['success' => false, 'error' => $result['error']], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Upload completed successfully',
            'path' => $result['path'],
        ]);
    }
}
