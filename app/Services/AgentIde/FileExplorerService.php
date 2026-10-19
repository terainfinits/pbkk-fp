<?php

namespace App\Services\AgentIde;

use Symfony\Component\Process\Process;

class FileExplorerService
{
    public function __construct(private WorkspaceService $workspace, private KernelRunnerService $kernels) {}

    public function basePath(): string
    {
        return $this->workspace->root();
    }

    /**
     * @param  array<string, string>  $extra
     * @return array<string, mixed>
     */
    private function operate(string $action, string $path, array $extra = []): array
    {
        $path = $this->workspace->normalize($path, $action === 'tree');
        $this->workspace->resolve($path, $action === 'tree');
        $runtime = $this->kernels->pythonRuntime();
        abort_unless($runtime !== null, 503, 'Python 3 is required for workspace file operations.');
        $process = new Process([$runtime['executable'], base_path('scripts/workspace_files.py')], null, ['PATH' => '/usr/bin:/bin']);
        $process->setInput(json_encode(['action' => $action, 'root' => $this->basePath(), 'path' => $path] + $extra, JSON_THROW_ON_ERROR));
        $process->setTimeout(10);
        $process->mustRun();
        $result = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        abort_unless($result['success'], $result['status'] ?? 500, $result['error'] ?? 'File operation failed.');

        return $result;
    }

    /** @return array<string, mixed> */
    public function scanTree(string $targetDir = ''): array
    {
        return $this->operate('tree', $targetDir);
    }

    /** @return array<string, mixed> */
    public function readFile(string $relativePath): array
    {
        return $this->operate('read', $relativePath);
    }

    /** @return array<string, mixed> */
    public function saveFile(string $relativePath, string $content): array
    {
        return $this->operate('save', $relativePath, ['content' => $content]);
    }

    /** @return array{ok: true, path: string} */
    public function createItem(string $relativePath, string $type, string $initialContent = ''): array
    {
        $result = $this->operate('create', $relativePath, ['type' => $type, 'content' => $initialContent]);

        return ['ok' => true, 'path' => $result['path']];
    }

    public function deleteItem(string $relativePath): string
    {
        return $this->operate('delete', $relativePath)['path'];
    }
}
