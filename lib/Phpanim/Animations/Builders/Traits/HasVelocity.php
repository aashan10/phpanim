<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders\Traits;

trait HasVelocity
{
    private mixed $velocity = null;

    /**
     * @param  float|callable(float): float  $value  Initial velocity at the start of the animation
     */
    public function velocity(callable|float $value): static
    {
        $this->velocity = $value;

        return $this;
    }
}
