<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Fiber;
use Phpanim\Animations\Builders\Traits\HasVelocity;
use Phpanim\Animations\Builders\Traits\ResolvesProperties;
use Phpanim\Raylib\Raylib;

/**
 * Animates a property starting at a given velocity with proportional drag (exponential decay).
 * Models fluid resistance — velocity decays asymptotically, never quite reaching zero,
 * so a small threshold is used to terminate.
 */
class InertiaBuilder extends Builder
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
     * @param  float|callable(float): float  $value  Drag coefficient (0–1 per second; higher = faster slowdown)
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
        $friction = $this->resolve($this->friction ?? 0.95);

        if (! is_float($from) || ! is_float($velocity) || ! is_float($friction)) {
            throw new \InvalidArgumentException("'from', 'velocity', and 'friction' must resolve to float values.");
        }

        return function () use ($from, $velocity, $friction) {
            $position = $from;

            while (abs($velocity) > 0.01) {
                $dt = (float) $this->raylib->GetFrameTime();

                // Exponential decay: velocity decays proportionally to itself each frame
                $velocity *= pow(1.0 - $friction, $dt);
                $position += $velocity * $dt;

                $this->write($this->property, $position);
                Fiber::suspend();
            }
        };
    }
}
