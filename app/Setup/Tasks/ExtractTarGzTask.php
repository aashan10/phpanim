<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;

readonly final class ExtractTarGzTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Extract Archive (.tar.gz)';
    }

    public function execute(Context $context): Context
    {
        $archivePath = $context->archivePath;

        $compressed = str_ends_with($archivePath, '.tar.gz') || str_ends_with($archivePath, '.tgz');


        if ($archivePath === null || !file_exists($archivePath)) {
            throw new \RuntimeException('Archive file does not exist.');
        }

        $phar = new \PharData($archivePath);
        $extractPath = dirname($archivePath);

        if ($compressed) {
            $extension = str_ends_with($archivePath, '.tar.gz') ? '.tar.gz' : '.tgz';
            $phar->decompress();
            $tarPath = str_replace($extension, '.tar', $archivePath);
            $phar = new \PharData($tarPath);
        }
        $phar->extractTo($extractPath, null, true);

        $folderName = $context->selectedAsset['name'] ?? '';
        $folderName = str_replace(['.tar.gz', '.tgz', '.tar'], '', $folderName);

        unlink($archivePath);
        if ($compressed) {
            $tarPath = str_replace(['.tar.gz', '.tgz'], '.tar', $archivePath);
            if (file_exists($tarPath)) {
                unlink($tarPath);
            }
        }

        return $context->with([
            'extractedPath' => sprintf("%s/%s", rtrim($extractPath, '/'), trim($folderName, '/'))
        ]);

        return $context;
    }
}
