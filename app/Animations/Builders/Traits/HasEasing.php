<?php

declare(strict_types=1);

namespace App\Animations\Builders\Traits;

use Closure;

trait HasEasing
{
    protected ?Closure $easing = null;

    /**
     * @param callable (float): float $easing
     */
    public function easing(callable $easing): static
    {
        $this->easing = \Closure::fromCallable($easing);

        return $this;
    }
}
