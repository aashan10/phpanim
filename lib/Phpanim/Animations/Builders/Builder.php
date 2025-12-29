<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Phpanim\Animations\Builders\BuilderInterface;
use Phpanim\Animations\Builders\Traits\HasTarget;
use Phpanim\Animations\Builders\Traits\ResolvesProperties;

abstract class Builder implements BuilderInterface
{
    use HasTarget, ResolvesProperties;

    public function tween(): TweenBuilder
    {
        return app()
            ->make(TweenBuilder::class, ['parent' => $this])
            ->target($this->target);
    }

    public function wait(float $duration = 1.0): WaitBuilder
    {
        return app()
            ->make(WaitBuilder::class, ['parent' => $this])
            ->duration($duration);
    }

}
