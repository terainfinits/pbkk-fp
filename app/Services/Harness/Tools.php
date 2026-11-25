<?php
// Fitur: Harness tugas coding
// connect with ../../../docs/HARNESS.md, ../../../tests/Feature/HarnessTest.php, ../../Jobs/RunCodingAgent.php
// connect with ./Repository.php

namespace App\Services\Harness;

use RuntimeException;
use Symfony\Component\Process\Process;

class Tools
{
    public function __construct(private Repository $repo) {}

    public function files(string $dir): array
    {
        return array_values(array_filter(explode("\0", $this->repo->git($dir, ['ls-files', '-z'])),
            fn ($p) => $p !== '' && !preg_match('~(?:^|/)(?:\.env(?:\.|$)|\.git(?:/|$)|vendor/|node_modules/)~i', $p)));
    }

    public function read(string $dir, string $path, int $start = 1, int $count = 100): string
    {
        $full = realpath($dir.DIRECTORY_SEPARATOR.$path);
        if (!in_array($path, $this->files($dir), true) || !$full ||
            !str_starts_with($full, realpath($dir).DIRECTORY_SEPARATOR) || is_link($dir.'/'.$path) || !is_file($full)) {
            throw new RuntimeException('File di luar cakupan.');
        }
        $file = new \SplFileObject($full);
        $file->seek(max(0, $start - 1));
        $lines = [];
        while (!$file->eof() && count($lines) < min(150, max(1, $count))) {
            $lines[] = substr($file->fgets(), 0, 2000);
        }
        return substr(implode('', $lines), 0, 12000);
    }

    public function search(string $dir, string $text): string
    {
        if ($text === '' || strlen($text) > 256 || str_contains($text, "\n")) {
            throw new RuntimeException('Query pencarian tidak valid.');
        }
        $hits = [];
        foreach ($this->files($dir) as $path) {
            $file = $dir.'/'.$path;
            if (is_link($file) || !is_file($file) || filesize($file) > 524288 ||
                !str_starts_with((string) realpath($file), realpath($dir).DIRECTORY_SEPARATOR)) {
                continue;
            }
            foreach (new \SplFileObject($file) as $i => $line) {
                if (is_string($line) && str_contains($line, $text)) {
                    $hits[] = $path.':'.($i + 1).' '.substr(trim($line), 0, 240);
                    if (count($hits) === 40) {
                        break 2;
                    }
                }
            }
        }
        return implode("\n", $hits);
    }

    public function test(string $dir, object $space, int $id, callable $check): array
    {
        // Image ENTRYPOINT defines the trusted project-specific test command.
        $name = 'laravel-harness-'.substr(hash('sha256', base_path()), 0, 12).'-'.$id;
        $process = new Process(['docker', 'run', '--rm', '--pull=never', '--name', $name,
            '--network=none', '--read-only', '--cap-drop=ALL', '--security-opt=no-new-privileges',
            '--user=65534:65534', '--memory=512m', '--cpus=1', '--pids-limit=128',
            '--tmpfs', '/tmp:rw,size=256m,mode=1777', '--mount', 'type=bind,src='.$dir.',dst=/source,readonly',
            '--workdir=/tmp', $space->image]);
        $process->setTimeout(90);
        $output = '';
        try {
            $process->start();
            while ($process->isRunning()) {
                $check();
                $process->checkTimeout();
                $output .= $process->getIncrementalOutput().$process->getIncrementalErrorOutput();
                if (strlen($output) > 64000) {
                    throw new RuntimeException('Output tes melebihi batas.');
                }
                usleep(100000);
            }
            $output .= $process->getIncrementalOutput().$process->getIncrementalErrorOutput();
            if (in_array($process->getExitCode(), [125, 126, 127], true)) {
                throw new RuntimeException('Periksa Docker, image lokal, dan entrypoint tes. '.substr($output, -2000));
            }
            return [$process->getExitCode(), substr($output, -12000)];
        } finally {
            if ($process->isRunning()) {
                $process->stop(0);
            }
            (new Process(['docker', 'rm', '-f', $name]))->setTimeout(5)->run();
        }
    }
}
