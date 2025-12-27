<?php

use App\Commands\SetupCommand;
use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use App\Setup\Context;
use App\Setup\Tasks\ChoosePlatformSpecificRaylibAssetTask;

beforeEach(function () {
    $this->task = new ChoosePlatformSpecificRaylibAssetTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $assets = collect([
        ['name' => 'raylib-5.0-linux-x86_64.tar.gz', 'browser_download_url' => 'https://example.com/linux-x86_64.tar.gz'],
        ['name' => 'raylib-5.0-linux-arm64.tar.gz', 'browser_download_url' => 'https://example.com/linux-arm64.tar.gz'],
        ['name' => 'raylib-5.0-macos-x86_64.tar.gz', 'browser_download_url' => 'https://example.com/macos-x86_64.tar.gz'],
        ['name' => 'raylib-5.0-macos-arm64.tar.gz', 'browser_download_url' => 'https://example.com/macos-arm64.tar.gz'],
        ['name' => 'raylib-5.0-win32-x86_64.zip', 'browser_download_url' => 'https://example.com/win32-x86_64.zip'],
    ]);

    $this->context = new Context(
        arch: CpuArchitecture::X86_64,
        os: OperatingSystem::LINUX,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest',
        assets: $assets
    );
});

it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Choosing platform-specific Raylib asset');
});

it('automatically selects matching asset for linux x86_64', function () {
    $result = $this->task->execute($this->context);

    expect($result->selectedAsset)->not->toBeNull();
    expect($result->selectedAsset['name'])->toBe('raylib-5.0-linux-x86_64.tar.gz');
});

it('automatically selects matching asset for macos arm64', function () {
    $contextMacosArm = $this->context->with([
        'os' => OperatingSystem::MACOS,
        'arch' => CpuArchitecture::ARM64,
    ]);

    $result = $this->task->execute($contextMacosArm);

    expect($result->selectedAsset)->not->toBeNull();
    expect($result->selectedAsset['name'])->toBe('raylib-5.0-macos-arm64.tar.gz');
});

it('automatically selects matching asset for windows x86_64', function () {
    $contextWindows = $this->context->with([
        'os' => OperatingSystem::WINDOWS,
        'arch' => CpuArchitecture::X86_64,
    ]);

    $result = $this->task->execute($contextWindows);

    expect($result->selectedAsset)->not->toBeNull();
    expect($result->selectedAsset['name'])->toBe('raylib-5.0-win32-x86_64.zip');
});

it('prompts user to choose when no exact arch match exists', function () {
    $contextNoArchMatch = $this->context->with(['arch' => CpuArchitecture::I386]);

    $this->mockIo->shouldReceive('choice')
        ->once()
        ->with(
            'Could not automatically detect a compatible Raylib asset for your platform. Please select one of the following options',
            ['raylib-5.0-linux-x86_64.tar.gz', 'raylib-5.0-linux-arm64.tar.gz']
        )
        ->andReturn('raylib-5.0-linux-arm64.tar.gz');

    $result = $this->task->execute($contextNoArchMatch);

    expect($result->selectedAsset)->not->toBeNull();
    expect($result->selectedAsset['name'])->toBe('raylib-5.0-linux-arm64.tar.gz');
});

it('filters assets by operating system first', function () {
    $linuxContext = $this->context->with(['os' => OperatingSystem::LINUX]);

    $result = $this->task->execute($linuxContext);

    expect($result->selectedAsset['name'])->toContain('linux');
    expect($result->selectedAsset['name'])->not->toContain('macos');
    expect($result->selectedAsset['name'])->not->toContain('win32');
});

it('preserves other context properties', function () {
    $result = $this->task->execute($this->context);

    expect($result->force)->toBe($this->context->force);
    expect($result->raylibVersion)->toBe($this->context->raylibVersion);
    expect($result->io)->toBe($this->context->io);
    expect($result->assets)->toBe($this->context->assets);
});

it('handles empty assets collection', function () {
    $emptyAssetsContext = $this->context->with(['assets' => collect()]);

    $this->mockIo->shouldReceive('choice')
        ->once()
        ->with(
            'Could not automatically detect a compatible Raylib asset for your platform. Please select one of the following options',
            []
        )
        ->andReturn(null);

    $result = $this->task->execute($emptyAssetsContext);

    expect($result->selectedAsset)->toBeNull();
});
