<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;

final readonly class ChoosePlatformSpecificRaylibAssetTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Choosing platform-specific Raylib asset';
    }

    public function execute(Context $context): Context
    {
        $os = $context->os->value;
        $arch = $context->arch->value;

        $osFilteredAssets = $context->assets->filter(fn ($asset) => str_contains($asset['name'], $os));
        $archFilteredAssets = $osFilteredAssets->filter(fn ($asset) => str_contains($asset['name'], $arch));

        if ($archFilteredAssets->isEmpty()) {
            $options = $osFilteredAssets->map(fn ($asset) => $asset['name'])->values();
            $selectedAssetName = $context->io->choice(
                'Could not automatically detect a compatible Raylib asset for your platform. Please select one of the following options',
                $options->toArray()
            );
            $selectedAsset = $osFilteredAssets->first(fn ($asset) => $asset['name'] === $selectedAssetName);
        } else {
            $selectedAsset = $archFilteredAssets->first();
        }

        $context = $context->with(['selectedAsset' => $selectedAsset]);

        return $context;
    }
}
