<?php

return [
    'workspace_root' => env('IDE_WORKSPACE_ROOT', base_path('folder-generated/users')),
    'runs_root' => storage_path('app/python_runs'),
    'legacy_owner_email' => env('IDE_LEGACY_OWNER_EMAIL'),
    'legacy_root' => base_path('folder-generated'),
    'sandbox_binary' => env('IDE_SANDBOX_BINARY', '/usr/bin/bwrap'),
    'sandbox_driver' => env('IDE_SANDBOX_DRIVER', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)
        ? 'native' : (PHP_OS_FAMILY === 'Windows' ? 'docker' : 'bubblewrap')),
    'python_binary' => env('IDE_PYTHON_BINARY'),
    'php_binary' => env('IDE_PHP_BINARY'),
    'node_binary' => env('IDE_NODE_BINARY'),
    'shell_binary' => env('IDE_SHELL_BINARY'),
    'docker_binary' => env('IDE_DOCKER_BINARY', 'docker'),
    'docker_image' => env('IDE_DOCKER_IMAGE', 'magentic-runtime:local'),
    'execution_seconds' => 300,
    'output_limit' => 1_000_000,
];
