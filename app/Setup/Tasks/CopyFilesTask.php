<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Enums\OperatingSystem;
use App\Setup\Context;
use Webmozart\Assert\Assert;

readonly final class CopyFilesTask implements TaskInterface
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
            OperatingSystem::MACOS   => 'libraylib.dylib',
            OperatingSystem::LINUX   => 'libraylib.so',
        };

        if (!$context->force && file_exists(storage_path('raylib/' . $destinationLibFileName)) && file_exists(storage_path('raylib/raylib.h'))) {
            return $context;
        }

        if ($extractedPath === null) {
            throw new \RuntimeException('Extracted path is not set in context.');
        }


        $destinationPath = storage_path('raylib');

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }


        $platformSpecificFilePattern = [
            OperatingSystem::WINDOWS->value => '/raylib.dll/',
            OperatingSystem::MACOS->value   => '/libraylib.(\d+(\.)*)+.dylib/',
            OperatingSystem::LINUX->value   => '/libraylib.(\d+(\.)*)+.so/',
        ];

        copy(from: $extractedPath . '/include/raylib.h', to: $destinationPath . '/raylib.h');

        $selectedPattern = $platformSpecificFilePattern[$context->os->value];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(sprintf("%s/%s", rtrim($extractedPath, '/'), 'lib') , \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );


        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match($selectedPattern, $file->getFilename())) {
                copy(from: $file->getPathname(), to: $destinationPath . '/' . $destinationLibFileName);
            }
        }

        Assert::fileExists($destinationPath . '/' . $destinationLibFileName, 'Failed to copy the platform-specific raylib file.');
        Assert::fileExists($destinationPath . '/raylib.h', 'Failed to copy raylib.h file.');

        return $context;
    }
}
