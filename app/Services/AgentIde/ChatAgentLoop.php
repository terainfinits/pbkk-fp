<?php

namespace App\Services\AgentIde;

class ChatAgentLoop
{
    public function __construct(private LlmProviderService $llm) {}

    public function run(string $provider, string $model, string $key, string $context, string $prompt, array $history, int $limit, ?callable $emit): array
    {
        $context .= "\nReview all requirements before finishing. End your reply on a separate line with [TASK_DONE] when all deliverables are complete, or [TASK_CONTINUE] when more work remains. Provide complete named file blocks. Later versions of the same path replace earlier versions.";
        $raw = '';
        $next = $prompt;
        $delta = $emit ? fn (string $text) => $emit('delta', ['text' => $text]) : null;
        for ($turn = 1; $turn <= $limit; $turn++) {
            set_time_limit(150);
            if ($emit) {
                $emit('iteration', ['current' => $turn, 'limit' => $limit]);
            }
            $result = $this->llm->dispatch($provider, $model, $key, $context, $next, $history, $delta);
            if (! $result['success']) {
                if ($turn > 1) {
                    unset($result['network_error']);
                }
                return $result;
            }
            $raw .= $result['text'];
            $marked = preg_match('/(?:^|\R)\[TASK_(DONE|CONTINUE)\]\s*$/', $raw, $match, PREG_OFFSET_CAPTURE);
            $truncated = $result['truncated'] ?? false;
            if ($marked) {
                $raw = substr($raw, 0, $match[0][1]);
            }
            // Older JSON clients accept a single unmarked response.
            if (! $truncated && (($marked && $match[1][0] === 'DONE') || (! $emit && ! $marked))) {
                break;
            }
            if ($turn === $limit) {
                return ['success' => true, 'text' => $raw, 'iterations' => $turn, 'stopReason' => 'iteration_limit'];
            }
            if (! $truncated) {
                $raw .= "\n\n";
            }
            if ($emit) {
                $emit('snapshot', ['text' => $raw]);
            }
            $next = "Original request:\n".$prompt."\n\nResponse so far:\n".$raw
                .($truncated ? "\nResume exactly at the interruption without repeating text." : "\nComplete the remaining requirements. Include only new work or corrected full files.");
        }

        return ['success' => true, 'text' => $raw, 'iterations' => $turn, 'stopReason' => 'completed'];
    }
}
