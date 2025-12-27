<?php

declare(strict_types=1);

namespace App\Setup;

use App\Commands\SetupCommand;
use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use Illuminate\Support\Collection;

final class Context
{
    /**
     * @param  null|array{name: string, browser_download_url: string}[]  $assets
     */
    public function __construct(
        public readonly ?CpuArchitecture $arch,
        public readonly ?OperatingSystem $os,
        public readonly SetupCommand $io,
        public readonly bool $force = false,
        public readonly string $raylibVersion = 'latest',
        public readonly Collection $assets = new Collection,
        public readonly ?array $selectedAsset = null,
        public readonly ?string $archivePath = null,
        public readonly ?string $extractedPath = null,
    ) {}

    public function with(array $changes): self
    {
        return new self(
            arch:          $this->differentiate($changes, 'arch'),
            os:            $this->differentiate($changes, 'os'),
            io:            $this->differentiate($changes, 'io'),
            force:         $this->differentiate($changes, 'force'),
            raylibVersion: $this->differentiate($changes, 'raylibVersion'),
            assets:        $this->differentiate($changes, 'assets'),
            selectedAsset: $this->differentiate($changes, 'selectedAsset'),
            archivePath:   $this->differentiate($changes, 'archivePath'),
            extractedPath: $this->differentiate($changes, 'extractedPath'),
        );
    }

    private function differentiate(array $changes, string $property): mixed
    {
        if (!isset($changes[$property])) {
            return $this->$property;
        }

        if ($changes[$property] !== $this->$property) {
            return $changes[$property];
        }

        return $this->$property;
    }
}
