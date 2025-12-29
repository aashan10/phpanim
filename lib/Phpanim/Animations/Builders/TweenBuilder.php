<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Phpanim\Animations\Builders\Traits\HasDuration;
use Phpanim\Animations\Builders\Traits\HasEasing;
use Phpanim\Animations\Builders\Traits\ManipulatesProperty;
use Phpanim\Animations\Builders\Traits\ResolvesProperties;
use Phpanim\Raylib\Raylib;
use Fiber;

class TweenBuilder extends Builder
{
    use ManipulatesProperty, HasDuration, HasEasing, ResolvesProperties;

    private mixed $from = null;
    private mixed $to = null;

    public function __construct(
        private AnimationBuilder|GroupBuilder $parent,
        private Raylib $raylib,
    )
    {}

    /**
     * @param float|callable(float): float $value
     */
    public function from(callable|float $value): self
    {
        $this->from = $value;
        return $this;
    }
    /**
     * @param float|callable(float): float $value
     */
    public function to(callable|float $value): self
    {
        $this->to = $value;
        return $this;
    }

    public function then(): AnimationBuilder|GroupBuilder
    {
        return $this->parent->add($this);
    }

    public function build(): callable
    {

        $from = $this->resolve($this->from);
        $to = $this->resolve($this->to);

        if (!is_float($from) || !is_float($to)) {
            throw new \InvalidArgumentException("The 'from' and 'to' values must resolve to float values.");
        }

        $duration = $this->resolve($this->duration);
        $easing = $this->resolve($this->easing);

        return function() use ($from, $to, $duration, $easing) {

            $t = 0.0;

            while ($t < 1.0) {
                $elapsed = $this->raylib->GetFrameTime();
                $t = min(1.0, (($t * $duration) + $elapsed) / $duration);

                $easedT = is_callable($easing) ? $easing($t) : $t;
                $value = $from + (($to - $from) * $easedT);

                $this->write($this->property, $value);
                Fiber::suspend();
            }
        };
    }
}
