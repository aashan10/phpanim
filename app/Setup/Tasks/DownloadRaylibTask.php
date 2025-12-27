<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;
use GuzzleHttp\RequestOptions;

readonly final class DownloadRaylibTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Downloading Raylib';
    }

    public function execute(Context $context): Context
    {
        $selectedAsset = $context->selectedAsset;

        if ($selectedAsset === null) {
            throw new \RuntimeException('No Raylib asset selected for download.');
        }

        $progressBar = null;

        $downloadUrl = $selectedAsset['browser_download_url'];

        $filename = $selectedAsset['name'];

        $extensions = ['.zip', '.tar.gz', '.tgz', '.tar'];

        $name = str_replace($extensions, '', $filename);

        if (is_dir(storage_path('raylib/' . $name)) && !$context->force) {
            return $context->with([
                'extractedPath' => storage_path('raylib/' . $filename),
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $client->request('GET', $downloadUrl, [
            RequestOptions::SINK => storage_path('raylib/' . $filename),
            RequestOptions::PROGRESS => function (
                $downloadTotal,
                $downloadedBytes,
                $uploadTotal,
                $uploadedBytes
            ) use(&$progressBar, $context) {
                if ($progressBar === null) {
                    $progressBar = $context->io->getOutput()->createProgressBar();
                    $progressBar->start();
                }

                $progressBar->setProgress($downloadedBytes);
            },
        ]);

        if ($progressBar) {
            $progressBar->finish();
            $context->io->newLine();
        }

        return $context->with([
            'archivePath' => storage_path('raylib/' . $filename),
        ]);
    }

}
