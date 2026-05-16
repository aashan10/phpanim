<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Enums\OperatingSystem;
use App\Setup\Context;
use App\Support\ConfigPath;
use Webmozart\Assert\Assert;

final readonly class CopyFilesTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Copying Files';
    }

    public function execute(Context $context): Context
    {
        $extractedPath = $context->extractedPath;

        $destinationLibFileName = match ($context->os) {
            OperatingSystem::WINDOWS => 'raylib.dll',
            OperatingSystem::MACOS => 'libraylib.dylib',
            OperatingSystem::LINUX => 'libraylib.so',
        };

        if (! $context->force && file_exists(ConfigPath::raylibPath($destinationLibFileName)) && file_exists(ConfigPath::raylibPath('raylib.h'))) {
            return $context;
        }

        if ($extractedPath === null) {
            throw new \RuntimeException('Extracted path is not set in context.');
        }

        $destinationPath = ConfigPath::raylibPath();
        $destinationLibPath = $destinationPath.'/'.$destinationLibFileName;

        if (! is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $platformSpecificFilePattern = [
            OperatingSystem::WINDOWS->value => '/^raylib\.dll$/i',
            OperatingSystem::MACOS->value => '/^libraylib(?:\.\d+(?:\.\d+)*)?\.dylib$/',
            OperatingSystem::LINUX->value => '/^libraylib(?:\.\d+(?:\.\d+)*)?\.so$/',
        ];

        copy(from: $extractedPath.'/include/raylib.h', to: $destinationPath.'/raylib.h');

        $selectedPattern = $platformSpecificFilePattern[$context->os->value];
        $copiedLibrary = false;

        if (file_exists($destinationLibPath)) {
            unlink($destinationLibPath);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(sprintf('%s/%s', rtrim($extractedPath, '/'), 'lib'), \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match($selectedPattern, $file->getFilename())) {
                $copiedLibrary = copy(from: $file->getPathname(), to: $destinationLibPath);
                if ($copiedLibrary) {
                    break;
                }
            }
        }

        Assert::true($copiedLibrary, 'Failed to copy the platform-specific raylib file.');
        Assert::fileExists($destinationLibPath, 'Failed to copy the platform-specific raylib file.');
        Assert::true(filesize($destinationLibPath) > 0, 'Copied raylib library file is empty.');
        Assert::fileExists($destinationPath.'/raylib.h', 'Failed to copy raylib.h file.');

        return $context;
    }
}
