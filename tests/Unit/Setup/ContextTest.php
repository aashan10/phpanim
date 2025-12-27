<?php

use App\Commands\SetupCommand;
use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use App\Setup\Context;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->mockIo = $this->mock(SetupCommand::class);
});

it('creates context with all required parameters', function () {
    $context = new Context(
        arch: CpuArchitecture::X86_64,
        os: OperatingSystem::LINUX,
        io: $this->mockIo,
        force: true,
        raylibVersion: '5.0'
    );

    expect($context->arch)->toBe(CpuArchitecture::X86_64);
    expect($context->os)->toBe(OperatingSystem::LINUX);
    expect($context->io)->toBe($this->mockIo);
    expect($context->force)->toBeTrue();
    expect($context->raylibVersion)->toBe('5.0');
    expect($context->assets)->toBeInstanceOf(Collection::class);
    expect($context->assets)->toBeEmpty();
    expect($context->selectedAsset)->toBeNull();
    expect($context->archivePath)->toBeNull();
    expect($context->extractedPath)->toBeNull();
});

it('creates context with default values', function () {
    $context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo
    );

    expect($context->arch)->toBeNull();
    expect($context->os)->toBeNull();
    expect($context->io)->toBe($this->mockIo);
    expect($context->force)->toBeFalse();
    expect($context->raylibVersion)->toBe('latest');
    expect($context->assets)->toBeInstanceOf(Collection::class);
    expect($context->assets)->toBeEmpty();
    expect($context->selectedAsset)->toBeNull();
    expect($context->archivePath)->toBeNull();
    expect($context->extractedPath)->toBeNull();
});

it('creates context with custom assets collection', function () {
    $assets = collect([
        ['name' => 'asset1.zip', 'url' => 'https://example.com/asset1.zip'],
        ['name' => 'asset2.tar.gz', 'url' => 'https://example.com/asset2.tar.gz'],
    ]);

    $context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        assets: $assets
    );

    expect($context->assets)->toBe($assets);
    expect($context->assets)->toHaveCount(2);
});

it('creates context with selected asset', function () {
    $selectedAsset = ['name' => 'raylib-5.0-linux-x86_64.tar.gz', 'url' => 'https://example.com/raylib.tar.gz'];

    $context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        selectedAsset: $selectedAsset
    );

    expect($context->selectedAsset)->toBe($selectedAsset);
});

it('creates context with archive and extracted paths', function () {
    $context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        archivePath: '/path/to/archive.tar.gz',
        extractedPath: '/path/to/extracted'
    );

    expect($context->archivePath)->toBe('/path/to/archive.tar.gz');
    expect($context->extractedPath)->toBe('/path/to/extracted');
});

it('creates new context with changes using with method', function () {
    $originalContext = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest'
    );

    $newContext = $originalContext->with([
        'arch' => CpuArchitecture::ARM64,
        'os' => OperatingSystem::MACOS,
        'force' => true,
        'raylibVersion' => '5.0',
    ]);

    expect($newContext)->not->toBe($originalContext);
    expect($newContext->arch)->toBe(CpuArchitecture::ARM64);
    expect($newContext->os)->toBe(OperatingSystem::MACOS);
    expect($newContext->force)->toBeTrue();
    expect($newContext->raylibVersion)->toBe('5.0');
    expect($newContext->io)->toBe($this->mockIo);

    expect($originalContext->arch)->toBeNull();
    expect($originalContext->os)->toBeNull();
    expect($originalContext->force)->toBeFalse();
    expect($originalContext->raylibVersion)->toBe('latest');
});

it('preserves unchanged properties when using with method', function () {
    $assets = collect(['asset1', 'asset2']);
    $selectedAsset = ['name' => 'test.zip'];

    $originalContext = new Context(
        arch: CpuArchitecture::X86_64,
        os: OperatingSystem::LINUX,
        io: $this->mockIo,
        force: true,
        raylibVersion: '5.0',
        assets: $assets,
        selectedAsset: $selectedAsset,
        archivePath: '/path/to/archive',
        extractedPath: '/path/to/extracted'
    );

    $newContext = $originalContext->with([
        'force' => false,
    ]);

    expect($newContext->arch)->toBe(CpuArchitecture::X86_64);
    expect($newContext->os)->toBe(OperatingSystem::LINUX);
    expect($newContext->io)->toBe($this->mockIo);
    expect($newContext->force)->toBeFalse();
    expect($newContext->raylibVersion)->toBe('5.0');
    expect($newContext->assets)->toBe($assets);
    expect($newContext->selectedAsset)->toBe($selectedAsset);
    expect($newContext->archivePath)->toBe('/path/to/archive');
    expect($newContext->extractedPath)->toBe('/path/to/extracted');
});

it('updates multiple properties at once with with method', function () {
    $originalAssets = collect(['old']);
    $newAssets = collect(['new']);
    $newSelectedAsset = ['name' => 'new.zip'];

    $originalContext = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        assets: $originalAssets
    );

    $newContext = $originalContext->with([
        'arch' => CpuArchitecture::ARM64,
        'assets' => $newAssets,
        'selectedAsset' => $newSelectedAsset,
        'archivePath' => '/new/path',
        'extractedPath' => '/new/extracted',
    ]);

    expect($newContext->arch)->toBe(CpuArchitecture::ARM64);
    expect($newContext->assets)->toBe($newAssets);
    expect($newContext->selectedAsset)->toBe($newSelectedAsset);
    expect($newContext->archivePath)->toBe('/new/path');
    expect($newContext->extractedPath)->toBe('/new/extracted');
});

it('context is immutable', function () {
    $context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo
    );

    $reflection = new \ReflectionClass($context);

    expect($reflection->isFinal())->toBeTrue();

    foreach ($reflection->getProperties() as $property) {
        expect($property->isReadOnly())->toBeTrue();
    }
});
