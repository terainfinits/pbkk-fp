<?php

namespace App\Services\Harness;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class AgentAction
{
    public static function parse(string $raw, array $checks): array
    {
        $action = json_decode($raw, true);
        $text = 'required|string|max:2000';
        $items = 'required|array|list|min:1|max:20';
        $rules = match ($action['tool'] ?? null) {
            'list', 'test' => [],
            'read' => ['path' => $text, 'start' => 'sometimes|integer|min:1', 'count' => 'sometimes|integer|min:1'],
            'search' => ['text' => $text],
            'edit' => ['path' => $text, 'old' => 'required|string|max:12000', 'new' => 'present|string|max:12000'],
            'patch' => ['patch' => 'required|string|max:100000'],
            'ask' => ['question' => $text],
            'plan' => ['goal' => $text, 'files' => $items, 'files.*' => $text,
                'steps' => $items, 'steps.*' => $text, 'criteria' => $items,
                'criteria.*' => 'required|array:description,check', 'criteria.*.description' => $text,
                'criteria.*.check' => ['required', 'string', Rule::in([...$checks, 'manual'])]],
            'finish' => ['summary' => $text, 'evidence' => $items, 'evidence.*' => $text],
            default => throw new RuntimeException('Return a JSON object with a supported tool.'),
        };
        $validator = Validator::make($action, ['tool' => 'required|string', ...$rules]);
        if ($validator->fails()) {
            throw new RuntimeException($validator->errors()->first());
        }
        $validated = $validator->validated();
        $progress = $action['progress'] ?? null;
        if (is_string($progress) && trim($progress) !== '' && mb_strlen($progress) <= 160) {
            $validated['progress'] = trim($progress);
        }
        return $validated;
    }
}
