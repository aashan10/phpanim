<?php

use App\Commands\SetupCommand;
use App\Setup\Context;
use App\Setup\Tasks\ExtractTarGzTask;

beforeEach(function () {
    $this->task = new ExtractTarGzTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $selectedAsset = [
        'name' => 'raylib-5.0-linux-x86_64.tar.gz',
    ];

    $this->context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest',
        selectedAsset: $selectedAsset,
        archivePath: null
    );

    $this->storagePath = storage_path('raylib');
    if (! is_dir($this->storagePath)) {
        mkdir($this->storagePath, 0755, true);
    }
});

afterEach(function () {
    if (isset($this->storagePath) && is_dir($this->storagePath)) {
        $files = glob($this->storagePath.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
});

it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Extract Archive (.tar.gz)');
});

it('throws exception when archive path is null', function () {
    expect(fn () => $this->task->execute($this->context))
        ->toThrow(\RuntimeException::class, 'Archive file does not exist.');
});

it('throws exception when archive file does not exist', function () {
    $contextWithNonExistentPath = $this->context->with(['archivePath' => '/non/existent/file.tar.gz']);

    expect(fn () => $this->task->execute($contextWithNonExistentPath))
        ->toThrow(\RuntimeException::class, 'Archive file does not exist.');
});

