<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders\Traits;

trait HasDuration
{
    protected float $duration = 1.0; // defaults to 1 second

    public function duration(float $duration): static
    {
        $this->duration = $duration;

        return $this;
    }
}
