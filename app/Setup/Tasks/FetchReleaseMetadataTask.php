<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;
use Illuminate\Support\Facades\Http;

final readonly class FetchReleaseMetadataTask implements TaskInterface
{
    private const RELEASES_API_URL = 'https://api.github.com/repos/raysan5/raylib/releases/tags/';
    private const LATEST_RELEASE_API_URL = 'https://api.github.com/repos/raysan5/raylib/releases/latest';

    public function getName(): string
    {
        return 'Fetching Release Metadata';
    }

    public function execute(Context $context): Context
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = Http::get(
            url: $context->raylibVersion === 'latest'
                ? self::LATEST_RELEASE_API_URL
                : self::RELEASES_API_URL . $context->raylibVersion,
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to fetch release metadata from GitHub API.');
        }

        $data = $response->getBody()->getContents();

        $metadata = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        $assets = collect($metadata['assets'] ?? []);

        return $context->with([
            'assets' => $assets,
        ]);

        return $context;
    }

}
