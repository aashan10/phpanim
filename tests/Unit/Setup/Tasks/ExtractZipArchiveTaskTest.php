<?php

use App\Commands\SetupCommand;
use App\Setup\Context;
use App\Setup\Tasks\ExtractZipArchiveTask;
use App\Support\ConfigPath;

beforeEach(function () {
    $this->task = new ExtractZipArchiveTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $selectedAsset = [
        'name' => 'raylib-5.0-win32-x86_64.zip',
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
        ->toBe('Extract Archive (.zip)');
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

it('throws exception when zip file cannot be opened', function () {
    $invalidZipPath = $this->configPath.'/invalid.zip';
    file_put_contents($invalidZipPath, 'not a zip file');

    $contextWithInvalidZip = $this->context->with(['archivePath' => $invalidZipPath]);

    expect(fn () => $this->task->execute($contextWithInvalidZip))
        ->toThrow(\RuntimeException::class, 'Failed to open zip archive.');
});

