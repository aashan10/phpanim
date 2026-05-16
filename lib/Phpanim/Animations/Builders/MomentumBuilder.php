<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Fiber;
use Phpanim\Animations\Builders\Traits\HasVelocity;
use Phpanim\Animations\Builders\Traits\ResolvesProperties;
use Phpanim\Raylib\Raylib;

/**
 * Animates a property starting at a given velocity with constant deceleration (linear friction).
 * Models sliding on a surface — velocity decreases at a fixed rate and stops exactly at zero.
 */
class MomentumBuilder extends Builder
{
    use HasVelocity, ResolvesProperties;

    private mixed $from = null;

    private mixed $friction = null;

    public function __construct(
        private AnimationBuilder|GroupBuilder $parent,
        private Raylib $raylib,
    ) {}

    /**
     * @param  float|callable(float): float  $value  Starting value of the property
     */
    public function from(callable|float $value): self
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @param  float|callable(float): float  $value  Constant deceleration rate in units/second²
     */
    public function friction(callable|float $value): self
    {
        $this->friction = $value;

        return $this;
    }

    public function then(): BuilderInterface
    {
        return $this->parent->add($this);
    }

    public function build(): callable
    {
        if ($this->from === null) {
            $this->from = $this->read($this->property);
        }

        $from = $this->resolve($this->from);
        $velocity = $this->resolve($this->velocity ?? 0.0);
        $friction = $this->resolve($this->friction ?? 500.0);

        if (! is_float($from) || ! is_float($velocity) || ! is_float($friction)) {
            throw new \InvalidArgumentException("'from', 'velocity', and 'friction' must resolve to float values.");
        }

        return function () use ($from, $velocity, $friction) {
            $position = $from;

            while (abs($velocity) > 0.0) {
                $dt = (float) $this->raylib->GetFrameTime();

                $deceleration = $friction * $dt;

                // Stop exactly at zero rather than overshooting
                if (abs($velocity) <= $deceleration) {
                    $velocity = 0.0;
                    break;
                }

                $velocity -= $deceleration * ($velocity > 0.0 ? 1.0 : -1.0);
                $position += $velocity * $dt;

                $this->write($this->property, $position);
                Fiber::suspend();
            }
        };
    }
}
