<?php

declare(strict_types=1);

namespace App\Setup;

use App\Commands\SetupCommand;
use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use Illuminate\Support\Collection;

final class Context
{
    public function __construct(
        public readonly ?CpuArchitecture $arch,
        public readonly ?OperatingSystem $os,
        public readonly SetupCommand $io,
        public readonly bool $force = false,
        public readonly string $raylibVersion = 'latest',
        public readonly Collection $assets = new Collection(),
        public readonly ?array $selectedAsset = null,
        public readonly ?string $archivePath = null,
    )
    {}
    public function with(array $changes): self
    {
        return new self(
            $changes['arch'] ?? $this->arch,
            $changes['os'] ?? $this->os,
            $changes['io'] ?? $this->io,
            $changes['force'] ?? $this->force,
            $changes['raylibVersion'] ?? $this->raylibVersion,
            $changes['assets'] ?? $this->assets,
            $changes['selectedAsset'] ?? $this->selectedAsset,
            $changes['archivePath'] ?? $this->archivePath,
        );
    }
}
