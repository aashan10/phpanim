<?php

use App\Commands\SetupCommand;
use App\Setup\Context;
use App\Setup\Tasks\ExtractTarGzTask;
use App\Support\ConfigPath;

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

    $this->configPath = ConfigPath::raylibPath();
    if (! is_dir($this->configPath)) {
        mkdir($this->configPath, 0755, true);
    }
});

afterEach(function () {
    if (isset($this->configPath) && is_dir($this->configPath)) {
        $files = glob($this->configPath.'/*');
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

