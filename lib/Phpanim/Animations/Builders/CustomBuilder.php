<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Fiber;
use Phpanim\Animations\Builders\Traits\HasDuration;
use Phpanim\Animations\Builders\Traits\ResolvesProperties;
use Phpanim\Raylib\Raylib;

class CustomBuilder extends Builder
{
    use HasDuration, ResolvesProperties;

    private mixed $callback = null;

    public function __construct(
        private AnimationBuilder|GroupBuilder $parent,
        private Raylib $raylib,
    ) {}

    public function callback(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function then(): BuilderInterface
    {
        return $this->parent->add($this);
    }

    public function build(): callable
    {
        if (! is_callable($this->callback)) {
            throw new \InvalidArgumentException('A callback must be provided for custom animations.');
        }

        $duration = $this->resolve($this->duration);
        $callback = $this->callback;

        return function () use ($duration, $callback) {
            $elapsed = 0.0;

            while ($elapsed < $duration) {
                $deltaTime = $this->raylib->GetFrameTime();
                $elapsed += $deltaTime;

                // Ensure we don't go over 1.0
                $progress = min(1.0, $elapsed / $duration);

                // Call the user's callback with target, deltaTime, and progress
                $callback($this->target, $deltaTime, $progress);

                // Don't suspend on the last frame
                if ($elapsed < $duration) {
                    Fiber::suspend();
                }
            }
        };
    }
}
