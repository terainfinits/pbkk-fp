<?php

namespace App\Services\AgentIde;

/**
 * Turns the raw text returned by a live LLM into the structured shape the
 * chat UI expects: reasoning steps, an extracted code block, and the file
 * path that code should be written to.
 */
class AgentResponseFormatter
{
    public function format(string $rawText, string $provider, string $model, ?string $targetFile, ?string $targetDirectory, string $prompt = ''): array
    {
        $defaultPath = $this->deriveTargetPath($rawText, $prompt, $targetFile, $targetDirectory);
        $files = $this->files($rawText, $defaultPath);
        $first = $files[0] ?? ['path' => $defaultPath, 'language' => 'plaintext', 'code' => ''];
        $steps = [];
        return [
            'success' => true,
            'provider' => $provider,
            'model' => $model,
            'raw' => $rawText,
            'files' => $files,
            'steps' => $steps,
            'code' => $first['code'],
            'language' => $first['language'],
            'targetPath' => $first['path'],
            'hasDiff' => $files !== [],
        ];
    }

    private function deriveTargetPath(string $rawText, string $prompt, ?string $targetFile, ?string $targetDirectory): string
    {
        $defaultFilename = 'GeneratedCode.php';
        if (preg_match('/([a-zA-Z0-9_\-]+\.(php|vue|js|ts|css|html|json|md))/i', $prompt, $pm)) {
            $defaultFilename = $pm[1];
        }

        $suggestedPath = $targetFile ?: ($targetDirectory ? rtrim($targetDirectory, '/\\') . '/' . $defaultFilename : 'app/Services/' . $defaultFilename);

        if (preg_match('/###\s*\[FILE:\s*([^\]]+)\]/i', $rawText, $matchPath)) {
            $suggestedPath = trim($matchPath[1]);
        }

        return $suggestedPath;
    }

    private function files(string $raw, string $defaultPath): array
    {
        $files = [];
        $firstSnippet = $path = $fence = null;
        $named = false;
        foreach (preg_split('/\r\n|\n|\r/', $raw) as $line) {
            if ($fence !== null) {
                if (! preg_match('/^ {0,3}'.preg_quote($fence[0], '/').'{'.strlen($fence).',}\s*$/', $line)) {
                    $lines[] = $line;
                    continue;
                }
                $file = ['path' => $path ?? $defaultPath, 'language' => $language, 'code' => implode("\n", $lines)];
                if ($path !== null) {
                    $files[$path] = $file;
                } else {
                    $firstSnippet ??= $file;
                }
                $fence = $path = null;
                continue;
            }
            if (preg_match('/^ {0,3}(\x60{3,}|~{3,})(.*)$/', $line, $match)) {
                $fence = $match[1];
                $language = strtok(trim($match[2]), " \t") ?: 'plaintext';
                $lines = [];
            } elseif (preg_match('/^\s*(?:#{1,6}\s*)?\[FILE:\s*([^\]]+)\]\s*$/i', $line, $match)
                || preg_match('/^\s*(?:#{1,6}\s+|\*\*|(?=File:)|(?=\x60))(?:File:\s*)?\x60?([\w.\/-]+\.[\w-]+)\x60?(?:\*\*)?\s*$/i', $line, $match)) {
                $path = trim($match[1], " \t\x60");
                $named = true;
            } elseif (trim($line) !== '') {
                $path = null;
            }
        }

        return $named ? array_values($files) : ($firstSnippet ? [$firstSnippet] : []);
    }
}
