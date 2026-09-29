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
        $suggestedPath = $this->deriveTargetPath($rawText, $prompt, $targetFile, $targetDirectory);
        [$language, $code] = $this->extractCodeBlock($rawText);

        $steps = [
            ['title' => 'Analyzing Requirements', 'detail' => "Parsed prompt using {$provider} ({$model}) and evaluated target context."],
            ['title' => 'Synthesizing Architecture', 'detail' => 'Crafted clean, optimized code logic following modern coding standards.'],
            ['title' => 'Code Generation', 'detail' => "Generated code artifact targeted for `{$suggestedPath}`."],
            ['title' => 'Written to Editor (Pending Review)', 'detail' => 'Code automatically written to the text editor. Review changes and click Accept or Reject in the editor.'],
        ];

        return [
            'success' => true,
            'provider' => $provider,
            'model' => $model,
            'raw' => $rawText,
            'steps' => $steps,
            'code' => $code,
            'language' => $language,
            'targetPath' => $suggestedPath,
            'hasDiff' => !empty($code),
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

    private function extractCodeBlock(string $rawText): array
    {
        if (preg_match('/```([a-zA-Z0-9_-]+)?\r?\n([\s\S]*?)```/', $rawText, $codeMatch)) {
            return [$codeMatch[1] ?: 'php', trim($codeMatch[2])];
        }

        return ['php', ''];
    }
}
