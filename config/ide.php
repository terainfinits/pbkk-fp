<?php

return [
    'workspace_root' => env('IDE_WORKSPACE_ROOT', base_path('folder-generated/users')),
    'runs_root' => storage_path('app/python_runs'),
    'legacy_owner_email' => env('IDE_LEGACY_OWNER_EMAIL'),
    'legacy_root' => base_path('folder-generated'),
    'sandbox_binary' => env('IDE_SANDBOX_BINARY', '/usr/bin/bwrap'),
    'execution_seconds' => 300,
    'output_limit' => 1_000_000,
];
