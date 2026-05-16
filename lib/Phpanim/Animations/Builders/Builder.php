<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

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

    public function custom(callable $callback, float $duration = 1.0): CustomBuilder
    {
        return app()
            ->make(CustomBuilder::class, ['parent' => $this])
            ->callback($callback)
            ->duration($duration)
            ->target($this->target);
    }

    public function manual(callable $callback): ManualBuilder
    {
        return app()
            ->make(ManualBuilder::class, ['parent' => $this])
            ->callback($callback)
            ->target($this->target);
    }

    public function spring(): SpringBuilder
    {
        return app()
            ->make(SpringBuilder::class, ['parent' => $this])
            ->target($this->target);
    }

    public function momentum(): MomentumBuilder
    {
        return app()
            ->make(MomentumBuilder::class, ['parent' => $this])
            ->target($this->target);
    }

    public function inertia(): InertiaBuilder
    {
        return app()
            ->make(InertiaBuilder::class, ['parent' => $this])
            ->target($this->target);
    }

    public function physics(): PhysicsBuilder
    {
        return app()
            ->make(PhysicsBuilder::class, ['parent' => $this])
            ->target($this->target);
    }
}
