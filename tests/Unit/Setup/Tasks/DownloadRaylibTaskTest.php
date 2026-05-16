<?php

use App\Commands\SetupCommand;
use App\Setup\Context;
use App\Setup\Tasks\DownloadRaylibTask;
use App\Support\ConfigPath;
use Illuminate\Console\OutputStyle;

beforeEach(function () {
    $this->task = new DownloadRaylibTask;
    $this->mockIo = $this->app->make(SetupCommand::class);
    $this->mockOutput = $this->mock(OutputStyle::class);

    $selectedAsset = [
        'name' => 'raylib-5.0_linux_amd64.tar.gz',
        'browser_download_url' => 'https://github.com/raysan5/raylib/releases/download/5.0/raylib-5.0_linux_amd64.tar.gz',
    ];

    $this->context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest',
        selectedAsset: $selectedAsset
    );

    $this->configPath = ConfigPath::raylibPath();
    if (! is_dir($this->configPath)) {
        mkdir($this->configPath, 0755, true);
    }
});

afterEach(function () {
    $files = glob($this->configPath.'/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
});

it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Downloading Raylib');
});
