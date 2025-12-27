<?php

use App\Commands\SetupCommand;
use App\Setup\Context;
use App\Setup\Tasks\ExtractArchiveTask;

beforeEach(function () {
    $this->task = new ExtractArchiveTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $this->context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest',
        archivePath: null
    );

    $this->storagePath = storage_path('raylib');
    if (! is_dir($this->storagePath)) {
        mkdir($this->storagePath, 0755, true);
    }
});

afterEach(function () {
    $files = glob($this->storagePath.'/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
});

it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Extract Archive');
});

it('skips extraction when extracted path exists and force is false', function () {
    $contextWithExtracted = $this->context->with(['extractedPath' => '/some/path']);

    $result = $this->task->execute($contextWithExtracted);

    expect($result)->toBe($contextWithExtracted);
});

it('throws exception when archive path is null', function () {
    expect(fn () => $this->task->execute($this->context))
        ->toThrow(\RuntimeException::class, 'Archive file does not exist.');
});

it('throws exception when archive file does not exist', function () {
    $contextWithNonExistentPath = $this->context->with(['archivePath' => '/non/existent/file.zip']);

    expect(fn () => $this->task->execute($contextWithNonExistentPath))
        ->toThrow(\RuntimeException::class, 'Archive file does not exist.');
});

it('throws exception for unsupported archive format', function () {
    $archivePath = $this->storagePath.'/test.rar';
    file_put_contents($archivePath, 'test content');

    $contextWithUnsupported = $this->context->with(['archivePath' => $archivePath]);

    expect(fn () => $this->task->execute($contextWithUnsupported))
        ->toThrow(\RuntimeException::class, 'Unsupported archive format: rar');
});
