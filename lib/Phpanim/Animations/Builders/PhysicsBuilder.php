<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Fiber;
use Phpanim\Animations\Builders\Traits\HasVelocity;
use Phpanim\Animations\Builders\Traits\ResolvesProperties;
use Phpanim\Raylib\Raylib;

/**
 * Animates a property under gravity with floor collision and restitution (bounce).
 * Models projectile/falling objects. Positive gravity increases the property value (downward).
 */
class PhysicsBuilder extends Builder
{
    use HasVelocity, ResolvesProperties;

    private mixed $from = null;

    private mixed $gravity = null;

    private mixed $bounce = null;

    private mixed $floor = null;

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
     * @param  float|callable(float): float  $value  Gravitational acceleration in units/second²
     */
    public function gravity(callable|float $value): self
    {
        $this->gravity = $value;

        return $this;
    }

    /**
     * @param  float|callable(float): float  $value  Restitution coefficient (0 = no bounce, 1 = perfect elasticity)
     */
    public function bounce(callable|float $value): self
    {
        $this->bounce = $value;

        return $this;
    }

    /**
     * @param  float|callable(float): float  $value  Floor position at which the object bounces or comes to rest
     */
    public function floor(callable|float $value): self
    {
        $this->floor = $value;

        return $this;
    }

    public function then(): BuilderInterface
    {
        return $this->parent->add($this);
    }

    public function build(): callable
    {
        if ($this->floor === null) {
            throw new \InvalidArgumentException('PhysicsBuilder requires a floor position. Call ->floor().');
        }

        if ($this->from === null) {
            $this->from = $this->read($this->property);
        }

        $from = $this->resolve($this->from);
        $velocity = $this->resolve($this->velocity ?? 0.0);
        $gravity = $this->resolve($this->gravity ?? 980.0);
        $bounce = $this->resolve($this->bounce ?? 0.6);
        $floor = $this->resolve($this->floor);

        if (! is_float($from) || ! is_float($velocity) || ! is_float($gravity) || ! is_float($bounce) || ! is_float($floor)) {
            throw new \InvalidArgumentException("'from', 'velocity', 'gravity', 'bounce', and 'floor' must resolve to float values.");
        }

        return function () use ($from, $velocity, $gravity, $bounce, $floor) {
            $position = $from;

            while (true) {
                $dt = (float) $this->raylib->GetFrameTime();

                $velocity += $gravity * $dt;
                $position += $velocity * $dt;

                if ($position >= $floor) {
                    $position = $floor;
                    $velocity = -$velocity * $bounce;

                    // Settled when the bounce would move less than 1 unit
                    if (abs($velocity) < 1.0) {
                        $this->write($this->property, $floor);
                        break;
                    }
                }

                $this->write($this->property, $position);
                Fiber::suspend();
            }
        };
    }
}
