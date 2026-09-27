<?php

namespace App\Services\AgentIde;

/**
 * Builds the system prompt sent to whichever LLM is handling the request.
 */
class AgentContextBuilder
{
    public function build(?string $targetDirectory, ?string $targetFile, ?string $currentCode): string
    {
        $lines = [
            "You are Antigravity AI, an autonomous agentic AI software engineer. You help write, modify, refactor, and create code in projects.",
            "Current Workspace Base: " . basename(base_path()),
        ];

        if ($targetDirectory) {
            $lines[] = "Active Target Directory: {$targetDirectory}";
        }

        if ($targetFile) {
            $lines[] = "Active File: {$targetFile}";
        }

        if ($currentCode) {
            $lines[] = "Current File Content:\n```\n" . substr($currentCode, 0, 4000) . "\n```";
        }

        $lines[] = "\nInstructions:";
        $lines[] = "1. Break down your reasoning into clear Agentic steps (Analysis, Plan, Code Implementation, Verification).";
        $lines[] = "2. When writing or modifying code, specify the target file path and provide the full runnable code in a code block with language identifier.";
        $lines[] = "3. If creating or modifying a file, clearly format with `### [FILE: path/to/file.ext]` before the code block.";
        $lines[] = "4. Be concise, precise, and state of the art in code quality.";

        return implode("\n", $lines);
    }
}
