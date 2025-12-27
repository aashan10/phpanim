<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ConfigPath;

enum OperatingSystem: string
{
    case WINDOWS = 'win32';
    case MACOS = 'macos';
    case LINUX = 'linux';

    public static function detect(): self
    {
        $os = PHP_OS_FAMILY;

        return match ($os) {
            'Windows' => self::WINDOWS,
            'Darwin' => self::MACOS,
            'Linux' => self::LINUX,
            default => throw new \RuntimeException('Unsupported operating system: '.$os),
        };
    }

    public function dllPath(): string
    {
        return match ($this) {
            self::WINDOWS => ConfigPath::raylibPath('raylib.dll'),
            self::MACOS => ConfigPath::raylibPath('libraylib.dylib'),
            self::LINUX => ConfigPath::raylibPath('libraylib.so'),
        };
    }
}
