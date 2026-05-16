<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Fiber;
use Phpanim\Animations\Builders\Traits\HasVelocity;
use Phpanim\Animations\Builders\Traits\ResolvesProperties;
use Phpanim\Raylib\Raylib;

class SpringBuilder extends Builder
{
    use HasVelocity, ResolvesProperties;

    private mixed $from = null;

    private mixed $to = null;

    private mixed $damping = null;

    private mixed $stiffness = null;

    public function __construct(
        private AnimationBuilder|GroupBuilder $parent,
        private Raylib $raylib,
    ) {}

    /**
     * @param  float|callable(float): float  $value
     */
    public function from(callable|float $value): self
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @param  float|callable(float): float  $value
     */
    public function to(callable|float $value): self
    {
        $this->to = $value;

        return $this;
    }

    /**
     * @param  float|callable(float): float  $value  How quickly oscillation dies down
     */
    public function damping(callable|float $value): self
    {
        $this->damping = $value;

        return $this;
    }

    /**
     * @param  float|callable(float): float  $value  Spring stiffness (higher = faster, stiffer)
     */
    public function stiffness(callable|float $value): self
    {
        $this->stiffness = $value;

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
        $to = $this->resolve($this->to);
        $damping = $this->resolve($this->damping ?? 10.0);
        $stiffness = $this->resolve($this->stiffness ?? 100.0);

        if (! is_float($from) || ! is_float($to)) {
            throw new \InvalidArgumentException("The 'from' and 'to' values must resolve to float values.");
        }

        $velocity = $this->resolve($this->velocity ?? 0.0);

        return function () use ($from, $to, $damping, $stiffness, $velocity) {
            $position = $from;

            while (true) {
                $dt = (float) $this->raylib->GetFrameTime();

                $force = -$stiffness * ($position - $to) - $damping * $velocity;
                $velocity += $force * $dt;
                $position += $velocity * $dt;

                $this->write($this->property, $position);

                if (abs($position - $to) < 0.01 && abs($velocity) < 0.01) {
                    $this->write($this->property, $to);
                    break;
                }

                Fiber::suspend();
            }
        };
    }
}
