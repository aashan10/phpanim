<?php

declare(strict_types=1);

namespace App\Enums;

enum CpuArchitecture: string
{
    case X86_64 = 'x86_64';
    case ARM64 = 'arm64';
    case I386 = 'i386';

    public static function detect(): self
    {
        $arch = PHP_INT_SIZE === 8 ? php_uname('m') : 'x86';

        return match ($arch) {
            'x86_64', 'AMD64' => self::X86_64,
            'aarch64', 'arm64' => self::ARM64,
            'i386', 'i486', 'i586', 'i686', 'x86' => self::I386,
            default => throw new \RuntimeException('Unsupported CPU architecture: '.$arch),
        };
    }
}
