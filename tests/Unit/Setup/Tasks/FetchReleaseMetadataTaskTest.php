<?php

use App\Commands\SetupCommand;
use App\Setup\Context;
use App\Setup\Tasks\FetchReleaseMetadataTask;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->task = new FetchReleaseMetadataTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $this->context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest'
    );
});

it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Fetching Release Metadata');
});

it('fetches latest release metadata successfully', function () {
    $mockResponse = [
        'tag_name' => '5.0',
        'assets' => [
            ['name' => 'raylib-5.0-linux-amd64.tar.gz', 'browser_download_url' => 'https://example.com/file1.tar.gz'],
            ['name' => 'raylib-5.0-macos-arm64.tar.gz', 'browser_download_url' => 'https://example.com/file2.tar.gz'],
        ],
    ];

    Http::fake([
        'https://api.github.com/repos/raysan5/raylib/releases/latest' => Http::response($mockResponse, 200),
    ]);

    $result = $this->task->execute($this->context);

    expect($result->assets)->toHaveCount(2);
    expect($result->assets->first()['name'])->toBe('raylib-5.0-linux-amd64.tar.gz');
});

it('fetches specific version release metadata successfully', function () {
    $mockResponse = [
        'tag_name' => '4.5',
        'assets' => [
            ['name' => 'raylib-4.5-linux-amd64.tar.gz', 'browser_download_url' => 'https://example.com/file1.tar.gz'],
        ],
    ];

    Http::fake([
        'https://api.github.com/repos/raysan5/raylib/releases/tags/4.5' => Http::response($mockResponse, 200),
    ]);

    $contextWithVersion = $this->context->with(['raylibVersion' => '4.5']);
    $result = $this->task->execute($contextWithVersion);

    expect($result->assets)->toHaveCount(1);
    expect($result->assets->first()['name'])->toBe('raylib-4.5-linux-amd64.tar.gz');
});

it('throws exception when API returns error status', function () {
    Http::fake([
        'https://api.github.com/repos/raysan5/raylib/releases/latest' => Http::response('Not Found', 404),
    ]);

    expect(fn () => $this->task->execute($this->context))
        ->toThrow(\RuntimeException::class, 'Failed to fetch release metadata from GitHub API.');
});

it('throws exception when response contains invalid JSON', function () {
    Http::fake([
        'https://api.github.com/repos/raysan5/raylib/releases/latest' => Http::response('invalid json', 200),
    ]);

    expect(fn () => $this->task->execute($this->context))
        ->toThrow(\JsonException::class);
});

it('handles empty assets array', function () {
    $mockResponse = [
        'tag_name' => '5.0',
        'assets' => [],
    ];

    Http::fake([
        'https://api.github.com/repos/raysan5/raylib/releases/latest' => Http::response($mockResponse, 200),
    ]);

    $result = $this->task->execute($this->context);

    expect($result->assets)->toBeEmpty();
});

it('preserves other context properties', function () {
    $mockResponse = [
        'tag_name' => '5.0',
        'assets' => [],
    ];

    Http::fake([
        'https://api.github.com/repos/raysan5/raylib/releases/latest' => Http::response($mockResponse, 200),
    ]);

    $result = $this->task->execute($this->context);

    expect($result->force)->toBe($this->context->force);
    expect($result->raylibVersion)->toBe($this->context->raylibVersion);
    expect($result->io)->toBe($this->context->io);
});
