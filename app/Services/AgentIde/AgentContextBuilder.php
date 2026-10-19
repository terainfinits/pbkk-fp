<?php

namespace App\Services\AgentIde;

/**
 * Builds the system prompt sent to whichever LLM is handling the request.
 */
class AgentContextBuilder
{
    public function __construct(private WorkspaceService $workspace) {}

    public function build(?string $targetDirectory, ?string $targetFile, ?string $currentCode): string
    {
        if ($targetDirectory) {
            $this->workspace->resolve($targetDirectory, true);
        }
        if ($targetFile) {
            $this->workspace->resolve($targetFile);
        }
        $lines = [
            "You are Magentic AI, an agentic software engineer. You help write, modify, refactor, and create code in the user's private workspace.",
            'Use relative paths within the current workspace only.',
        ];

        if ($targetDirectory) {
            $lines[] = "Active Target Directory: {$targetDirectory}";
        }

        if ($targetFile) {
            $lines[] = "Active File: {$targetFile}";
        }

        if ($currentCode) {
            $lines[] = "Current File Content:\n```\n".substr($currentCode, 0, 4000)."\n```";
        }

        $lines[] = "\nInstructions:";
        $lines[] = '1. Break down your reasoning into clear Agentic steps (Analysis, Plan, Code Implementation, Verification).';
        $lines[] = '2. When writing or modifying code, specify the target file path and provide the full runnable code in a code block with language identifier.';
        $lines[] = '3. If creating or modifying a file, clearly format with `### [FILE: path/to/file.ext]` before the code block.';
        $lines[] = '4. Be concise, precise, and state of the art in code quality.';

        return implode("\n", $lines);
    }
}
