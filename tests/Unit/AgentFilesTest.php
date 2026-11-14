<?php

use App\Services\AgentIde\AgentResponseFormatter;

it('preserves all named files and the complete raw response', function () {
    $raw = "Penjelasan\n### [FILE: data_loader.py]\n```python\nprint('load')\n```\n### [FILE: tests/test_loader.py]\n```python\nassert True\n```";
    $result = (new AgentResponseFormatter)->format($raw, 'senopati', 'qwen3:14b', null, null);
    expect(array_column($result['files'], 'path'))->toBe(['data_loader.py', 'tests/test_loader.py']);
    expect($result['files'][1]['code'])->toBe('assert True');
    expect($result['raw'])->toBe($raw);
});
