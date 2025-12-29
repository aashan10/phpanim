<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Phpanim\Animations\Builders\AnimationBuilder;
use Phpanim\Animations\Builders\Traits\HasTarget;
use Fiber;

class GroupBuilder extends Builder
{
    use HasTarget;

    public function __construct(
        private AnimationBuilder $parent,
    )
    {}

    private bool $isParallel = false;
    private array $animations = [];

    public function then(): AnimationBuilder
    {
        return $this->parent->add($this);
    }

    public function parallel(): self
    {
        $this->isParallel = true;
        return $this;
    }

    public function sequential(): self
    {
        $this->isParallel = false;
        return $this;
    }
    /**
     * @param callable(AnimationBuilder): mixed $callback
     */
    public function group(callable $callback): self
    {
        $animationBuilder = clone $this->parent;
        $animationBuilder->reset();
        $animationBuilder->target($this->target);
        $callback($animationBuilder);


        $this->animations = $animationBuilder->animations();
        return $this;
    }

    public function add(BuilderInterface $animation): self
    {
        $this->animations[] = $animation->build();
        return $this;
    }

    public function build(): callable
    {
        return match($this->isParallel) {
            true => $this->buildParallel(),
            false => $this->buildSequential(),
        };
    }

    private function buildParallel(): callable
    {
        $animations = $this->animations;

        return function () use ($animations) {
            $fibers = [];
            foreach ($animations as $animation) {
                $fibers[] = new Fiber($animation);
            }

            foreach ($fibers as $fiber) {
                $fiber->start();
            }

            while (!empty($fibers)) {
                foreach ($fibers as $index => $fiber) {
                    if ($fiber->isSuspended()) {
                        $fiber->resume();
                    }

                    if ($fiber->isTerminated()) {
                        unset($fibers[$index]);
                    }
                }

                if (!empty($fibers)) {
                    Fiber::suspend();
                }
            }
        };
    }

    private function buildSequential(): callable
    {
        return function () {
            foreach ($this->animations as $animation) {
                $animation();
            }
        };
    }
}
