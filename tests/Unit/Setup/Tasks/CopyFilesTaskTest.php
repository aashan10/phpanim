<?php

use App\Commands\SetupCommand;
use App\Enums\OperatingSystem;
use App\Setup\Context;
use App\Setup\Tasks\CopyFilesTask;
use App\Support\ConfigPath;

beforeEach(function () {
    $this->task = new CopyFilesTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $this->context = new Context(
        arch: null,
        os: OperatingSystem::LINUX,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest',
        extractedPath: null
    );

    $this->configPath = ConfigPath::raylibPath();
    if (! is_dir($this->configPath)) {
        mkdir($this->configPath, 0755, true);
    }

});


it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Copying Files');
});


