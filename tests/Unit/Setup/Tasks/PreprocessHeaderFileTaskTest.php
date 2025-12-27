<?php

use App\Commands\SetupCommand;
use App\Enums\OperatingSystem;
use App\Setup\Context;
use App\Setup\Tasks\PreprocessHeaderFileTask;
use App\Support\ConfigPath;

beforeEach(function () {
    $this->task = new PreprocessHeaderFileTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $this->context = new Context(
        arch: null,
        os: OperatingSystem::LINUX,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest'
    );

    $this->configPath = ConfigPath::raylibPath();
    if (! is_dir($this->configPath)) {
        mkdir($this->configPath, 0755, true);
    }

    $this->headerPath = $this->configPath.'/raylib.h';
    file_put_contents($this->headerPath, '');
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
        ->toBe('Preprocessing Header File');
});

it('shows warning for windows platform', function () {
    $contextWindows = $this->context->with(['os' => OperatingSystem::WINDOWS]);

    $this->mockIo->shouldReceive('warn')
        ->once()
        ->with(\Mockery::type('string'));

    $this->mockIo->shouldReceive('info')
        ->once()
        ->with('File to be processed: '.$this->headerPath);

    $result = $this->task->execute($contextWindows);

    expect($result)->toBe($contextWindows);
});

it('processes header file on linux platform', function () {
    file_put_contents($this->headerPath, "#define TEST 1\n// Comment\nvoid function();");

    $result = $this->task->execute($this->context);

    expect($result)->toBe($this->context);
    expect(file_exists($this->headerPath))->toBeTrue();
});

it('processes header file on macos platform', function () {
    $contextMacos = $this->context->with(['os' => OperatingSystem::MACOS]);
    file_put_contents($this->headerPath, "#define TEST 1\n// Comment\nvoid function();");

    $result = $this->task->execute($contextMacos);

    expect($result)->toBe($contextMacos);
    expect(file_exists($this->headerPath))->toBeTrue();
});

it('preserves other context properties', function () {
    $contextWindows = $this->context->with(['os' => OperatingSystem::WINDOWS]);

    $this->mockIo->shouldReceive('warn')->once();
    $this->mockIo->shouldReceive('info')->once();

    $result = $this->task->execute($contextWindows);

    expect($result->force)->toBe($this->context->force);
    expect($result->raylibVersion)->toBe($this->context->raylibVersion);
    expect($result->io)->toBe($this->context->io);
});
