<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;

readonly final class ExtractZipArchiveTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Extract Archive (.zip)';
    }

    public function execute(Context $context): Context
    {
        $archivePath = $context->archivePath;
        if ($archivePath === null || !file_exists($archivePath)) {
            throw new \RuntimeException('Archive file does not exist.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($archivePath) !== true) {
            throw new \RuntimeException('Failed to open zip archive.');
        }

        $extractPath = dirname($archivePath) . '/raylib';
        $zip->extractTo($extractPath);
        $zip->close();

        $folderName = $context->selectedAsset['name'] ?? '';

        $folderName = str_replace('.zip', '', $folderName);

        unlink($archivePath);

        return $context->with([
            'extractedPath' => sprintf("%s/%s", rtrim($extractPath, '/'), trim($folderName, '/'))
        ]);
    }
}
