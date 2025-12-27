<?php

use App\Commands\SetupCommand;
use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use App\Setup\Context;
use App\Setup\Tasks\CheckPlatformRequirementsTask;
use Illuminate\Console\OutputStyle;

beforeEach(function () {
    $this->task = new CheckPlatformRequirementsTask;
    $this->mockIo = $this->mock(SetupCommand::class);
    $this->mockOutput = $this->mock(OutputStyle::class);

    $this->mockIo->shouldReceive('getOutput')->andReturn($this->mockOutput);

    $this->context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest'
    );
});

it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Checking Platform Requirements');
});

it('creates raylib directory if it does not exist', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(false);

    $storagePath = storage_path('raylib');
    if (is_dir($storagePath)) {
        rmdir($storagePath);
    }

    $result = $this->task->execute($this->context);

    expect(is_dir($storagePath))->toBeTrue();
    expect($result)->toBeInstanceOf(Context::class);
});

it('detects operating system when not provided', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(false);

    $result = $this->task->execute($this->context);

    expect($result->os)->toBeInstanceOf(OperatingSystem::class);
    expect($result->os)->not->toBeNull();
});

it('detects cpu architecture when not provided', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(false);

    $result = $this->task->execute($this->context);

    expect($result->arch)->toBeInstanceOf(CpuArchitecture::class);
    expect($result->arch)->not->toBeNull();
});

it('preserves provided operating system', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(false);

    $contextWithOs = $this->context->with(['os' => OperatingSystem::LINUX]);
    $result = $this->task->execute($contextWithOs);

    expect($result->os)->toBe(OperatingSystem::LINUX);
});

it('preserves provided cpu architecture', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(false);

    $contextWithArch = $this->context->with(['arch' => CpuArchitecture::ARM64]);
    $result = $this->task->execute($contextWithArch);

    expect($result->arch)->toBe(CpuArchitecture::ARM64);
});

it('outputs verbose information when verbose mode is enabled', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(true);
    $this->mockIo->shouldReceive('info')->times(2);

    $result = $this->task->execute($this->context);

    expect($result)->toBeInstanceOf(Context::class);
});

it('skips verbose output when verbose mode is disabled', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(false);
    $this->mockIo->shouldReceive('info')->never();

    $result = $this->task->execute($this->context);

    expect($result)->toBeInstanceOf(Context::class);
});

it('preserves other context properties', function () {
    $this->mockOutput->shouldReceive('isVerbose')->andReturn(false);

    $result = $this->task->execute($this->context);

    expect($result->force)->toBe($this->context->force);
    expect($result->raylibVersion)->toBe($this->context->raylibVersion);
    expect($result->io)->toBe($this->context->io);
});
