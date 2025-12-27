<?php

use App\Enums\CpuArchitecture;

it('has correct enum values', function () {
    expect(CpuArchitecture::X86_64->value)->toBe('x86_64');
    expect(CpuArchitecture::ARM64->value)->toBe('arm64');
    expect(CpuArchitecture::I386->value)->toBe('i386');
});

it('can detect current architecture', function () {
    $detected = CpuArchitecture::detect();

    expect($detected)->toBeInstanceOf(CpuArchitecture::class);
    expect($detected)->toBeIn([
        CpuArchitecture::X86_64,
        CpuArchitecture::ARM64,
        CpuArchitecture::I386,
    ]);
});

it('detection logic handles different architecture strings', function () {
    // We can't easily mock php_uname, but we can test the match logic indirectly
    // by knowing what the current system returns
    $currentArch = php_uname('m');
    $detected = CpuArchitecture::detect();

    if (in_array($currentArch, ['x86_64', 'AMD64'])) {
        expect($detected)->toBe(CpuArchitecture::X86_64);
    } elseif (in_array($currentArch, ['aarch64', 'arm64'])) {
        expect($detected)->toBe(CpuArchitecture::ARM64);
    } elseif (in_array($currentArch, ['i386', 'i486', 'i586', 'i686', 'x86'])) {
        expect($detected)->toBe(CpuArchitecture::I386);
    }
});
