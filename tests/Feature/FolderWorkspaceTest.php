<?php

use App\Services\Harness\FolderWorkspace;
use App\Services\Harness\Repository;
use Illuminate\Support\Facades\File;

it('reviews a private snapshot and applies all files only with matching originals', function (bool $empty) {
    $dir = storage_path('framework/testing/folder-'.uniqid());
    $source = $dir.'/source';
    $copy = $dir.'/snapshot';
    File::ensureDirectoryExists($source);
    $git = app(Repository::class);
    $folder = app(FolderWorkspace::class);
    try {
        if (!$empty) file_put_contents($source.'/score.js', 'original');
        file_put_contents($source.'/.env', 'private');
        $folder->snapshot($git, $source, $copy);
        expect(is_file($copy.'/.env'))->toBeFalse();
        file_put_contents($copy.'/score.js', 'fixed');
        file_put_contents($copy.'/game.js', 'game');
        $git->git($copy, ['add','.']);
        $run = (object)['path'=>$source, 'changes'=>json_encode($git->changes($copy)), 'patch'=>$git->patch($copy)];
        file_put_contents($source.'/score.js', 'local');
        expect(fn () => $folder->apply($git, $run))->toThrow(RuntimeException::class, 'score.js');
        expect(file_exists($source.'/game.js'))->toBeFalse();
        if ($empty) unlink($source.'/score.js');
        else file_put_contents($source.'/score.js', 'original');
        $folder->apply($git, $run);
        expect(file_get_contents($source.'/game.js'))->toBe('game')
            ->and(file_get_contents($source.'/score.js'))->toBe('fixed')
            ->and(file_exists($source.'/.git'))->toBeFalse();
    } finally {
        File::deleteDirectory($dir);
    }
})->with([true,false]);
