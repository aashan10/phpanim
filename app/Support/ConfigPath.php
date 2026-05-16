<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\OperatingSystem;

class ConfigPath
{
    public static function get(string $subPath = ''): string
    {
        $baseDir = match (OperatingSystem::detect()) {
            OperatingSystem::WINDOWS => $_SERVER['APPDATA'] ?? $_SERVER['USERPROFILE'].'\\AppData\\Roaming',
            OperatingSystem::MACOS, OperatingSystem::LINUX => $_SERVER['HOME'].'/.config',
        };

        $configDir = $baseDir.DIRECTORY_SEPARATOR.'phpanim';

        if ($subPath) {
            $configDir .= DIRECTORY_SEPARATOR.ltrim($subPath, DIRECTORY_SEPARATOR);
        }

        return $configDir;
    }

    public static function raylibPath(string $subPath = ''): string
    {
        return self::get('raylib'.($subPath ? DIRECTORY_SEPARATOR.$subPath : ''));
    }
}
