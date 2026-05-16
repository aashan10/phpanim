<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Enums\OperatingSystem;
use App\Setup\Context;
use App\Support\ConfigPath;

final readonly class MacOsSkipQuarantineTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Skipping macOS Quarantine';
    }

    public function execute(Context $context): Context
    {
        match ($context->os) {
            OperatingSystem::MACOS => $this->skipQuarantine($context),
            default => $context->io->info('Skipping macOS quarantine step as the operating system is not macOS.'),
        };

        return $context;
    }

    private function skipQuarantine(Context $context): Context
    {
        $raylibPath = ConfigPath::raylibPath('libraylib.dylib');

        if (! file_exists($raylibPath)) {
            $context->io->error('Raylib library not found at the expected path: '.$raylibPath);
            throw new \RuntimeException('Raylib library is missing.');
        }

        $command = sprintf('xattr -d com.apple.quarantine %s', escapeshellarg($raylibPath));
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $context->io->error('Failed to remove quarantine attribute from raylib library. Please ensure that you have the necessary permissions to modify the file attributes.');
            throw new \RuntimeException('Removing quarantine attribute failed.');
        }

        return $context;
    }
}
