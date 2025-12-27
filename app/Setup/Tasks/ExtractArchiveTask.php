<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;

final readonly class ExtractArchiveTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Extract Archive';
    }

    public function execute(Context $context): Context
    {
        if ($context->extractedPath !== null && ! $context->force) {
            return $context;
        }

        $archivePath = $context->archivePath;

        if ($archivePath === null || ! file_exists($archivePath)) {
            throw new \RuntimeException('Archive file does not exist.');
        }

        $extension = pathinfo($archivePath, PATHINFO_EXTENSION);

        return match ($extension) {
            'zip' => app()->make(ExtractZipArchiveTask::class)->execute($context),
            'tar', 'gz', 'bz2', 'xz' => app()->make(ExtractTarGzTask::class)->execute($context),
            default => throw new \RuntimeException('Unsupported archive format: '.$extension),
        };
    }
}
