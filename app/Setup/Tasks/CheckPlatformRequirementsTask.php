<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use App\Setup\Context;

readonly final class CheckPlatformRequirementsTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Checking Platform Requirements';
    }

    public function execute(Context $context): Context
    {
        $output = $context->io->getOutput();
        $verbose = $output->isVerbose();

        $raylibDir = storage_path('raylib');

        if (!is_dir($raylibDir)) {
            mkdir($raylibDir, 0755, true);
            if ($verbose) {
                $context->io->info("\nCreated directory for Raylib assets at: " . $raylibDir);
            }
        }

        if ($context->os === null) {
            $os = OperatingSystem::detect();
            $context = $context->with(['os' => $os]);

            if ($verbose) {
                $context->io->info("\nDetected Operating System: " . $os->value);
            }
        }

        if ($context->arch === null) {
            $arch = CpuArchitecture::detect();
            $context = $context->with(['arch' => $arch]);

            if ($verbose) {
                $context->io->info("\nDetected CPU Architecture: " . $arch->value);
            }
        }

        return $context;
    }
}
