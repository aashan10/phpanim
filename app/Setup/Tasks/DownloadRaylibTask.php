<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;
use App\Support\ConfigPath;
use GuzzleHttp\RequestOptions;

final readonly class DownloadRaylibTask implements TaskInterface
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

        if (is_dir(ConfigPath::raylibPath($name)) && ! $context->force) {
            return $context->with([
                'extractedPath' => ConfigPath::raylibPath($filename),
            ]);
        }

        $client = new \GuzzleHttp\Client;
        $client->request('GET', $downloadUrl, [
            RequestOptions::SINK => ConfigPath::raylibPath($filename),
            RequestOptions::PROGRESS => function (
                $downloadTotal,
                $downloadedBytes,
                $uploadTotal,
                $uploadedBytes
            ) use (&$progressBar, $context) {
                if ($progressBar === null) {
                    $progressBar = $context->io->getOutput()?->createProgressBar();
                    $progressBar?->start();
                }

                $progressBar?->setProgress($downloadedBytes);
            },
        ]);

        if ($progressBar) {
            $progressBar->finish();
            $context->io->newLine();
        }

        return $context->with([
            'archivePath' => ConfigPath::raylibPath($filename),
        ]);
    }
}
