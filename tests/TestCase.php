<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    private string $testDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDirectory = storage_path('framework/testing/native space '.bin2hex(random_bytes(10)));
        config(['ide.workspace_root' => $this->testDirectory.'/workspaces', 'ide.runs_root' => $this->testDirectory.'/runs', 'ide.legacy_owner_email' => null]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->testDirectory);
        parent::tearDown();
    }
}
