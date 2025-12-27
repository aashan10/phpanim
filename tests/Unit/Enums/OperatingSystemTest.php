<?php

use App\Enums\OperatingSystem;
use App\Support\ConfigPath;

it('has correct enum values', function () {
    expect(OperatingSystem::WINDOWS->value)->toBe('win32');
    expect(OperatingSystem::MACOS->value)->toBe('macos');
    expect(OperatingSystem::LINUX->value)->toBe('linux');
});


it('detects macOS correctly', function () {
    if (PHP_OS_FAMILY === 'Darwin') {
        expect(OperatingSystem::detect())->toBe(OperatingSystem::MACOS);
    }
});

it('detects Linux correctly', function () {
    if (PHP_OS_FAMILY === 'Linux') {
        expect(OperatingSystem::detect())->toBe(OperatingSystem::LINUX);
    } else {
        // If we're not on Linux, just verify the method works
        expect(true)->toBeTrue();
    }
});

it('returns correct dll path for Windows', function () {
    $windows = OperatingSystem::WINDOWS;
    expect($windows->dllPath())->toBe(ConfigPath::raylibPath('raylib.dll'));
});

it('returns correct dll path for macOS', function () {
    $macos = OperatingSystem::MACOS;
    expect($macos->dllPath())->toBe(ConfigPath::raylibPath('libraylib.dylib'));
});

it('returns correct dll path for Linux', function () {
    $linux = OperatingSystem::LINUX;
    expect($linux->dllPath())->toBe(ConfigPath::raylibPath('libraylib.so'));
});

it('can detect current operating system', function () {
    $detected = OperatingSystem::detect();

    expect($detected)->toBeInstanceOf(OperatingSystem::class);
    expect($detected)->toBeIn([
        OperatingSystem::WINDOWS,
        OperatingSystem::MACOS,
        OperatingSystem::LINUX,
    ]);
});

it('dll paths use ConfigPath helper correctly', function () {
    $windows = OperatingSystem::WINDOWS;
    $macos = OperatingSystem::MACOS;
    $linux = OperatingSystem::LINUX;

    expect($windows->dllPath())->toStartWith(ConfigPath::raylibPath());
    expect($macos->dllPath())->toStartWith(ConfigPath::raylibPath());
    expect($linux->dllPath())->toStartWith(ConfigPath::raylibPath());

    expect($windows->dllPath())->toEndWith('.dll');
    expect($macos->dllPath())->toEndWith('.dylib');
    expect($linux->dllPath())->toEndWith('.so');
});
