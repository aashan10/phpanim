<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;

final readonly class CleanupDownloadsTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Cleanup Downloads';
    }

    public function execute(Context $context): Context
    {
        $extractedPath = $context->extractedPath;

        if ($extractedPath !== null && is_dir($extractedPath)) {

            $this->deleteDirectory($extractedPath);
        }

        return $context;
    }

    private function deleteDirectory(string $extractedPath)
    {
        $files = array_diff(scandir($extractedPath) ?? [], ['.', '..']);
        foreach ($files as $file) {
            $filePath = $extractedPath.DIRECTORY_SEPARATOR.$file;
            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($extractedPath);
    }
}
