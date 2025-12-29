<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Phpanim\Animations\AnimationQueue;
use Phpanim\Animations\Builders\Traits\HasTarget;
use Phpanim\Events\EventDispatcher;
use Phpanim\Raylib\Raylib;


class AnimationBuilder extends Builder
{
    use HasTarget;

    protected array $animations = [];

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private AnimationQueue $queue
    )
    {}

    public static function make(): self
    {
        return app()->make(self::class);
    }

    public function then(): AnimationBuilder
    {
        return $this;
    }

    public function build(): callable
    {
        $animations = $this->animations;

        return function () use ($animations) {
            foreach ($animations as $animation) {
                $fiber = new \Fiber($animation);
                $fiber->start();

                while ($fiber->isSuspended()) {
                    $fiber->resume();
                }
            }
        };
    }

    public function add(BuilderInterface $animation): self
    {
        $this->animations[] = $animation->build();
        return $this;
    }

    public function animations(): array
    {
        return $this->animations;
    }

    public function group(callable $callback): GroupBuilder
    {
        $builder = app()->make(GroupBuilder::class, ['parent' => $this]);
        $builder->target($this->target);

        $builder->group($callback);
        return $builder;
    }

    public function reset(): void
    {
        $this->animations = [];
        $this->target = [];
    }

    public function on(string $eventName, callable $callback): self
    {
        $this->dispatcher->on($eventName, function($eventData) use ($callback, $eventName) {
            $animationBuilder = clone $this;
            $animationBuilder->reset();
            $animationBuilder->target($this->target);


            $callback($animationBuilder, $eventData);

            if (!empty($animationBuilder->animations())) {
                $key = $eventName . '_' . uniqid();
                $animation = function() use ($animationBuilder) {
                    foreach ($animationBuilder->animations() as $anim) {
                        $anim();
                    }
                };

                $this->queue->addImmediateAnimation($key, $animation);
            }
        });
        return $this;
    }

    public function waitFor(string $eventName): self
    {
        $waitAnimation = function() use ($eventName) {
            $eventReceived = false;

            $listener = function() use (&$eventReceived) {
                $eventReceived = true;
            };

            $this->dispatcher->once($eventName, $listener);

            while (!$eventReceived) {
                \Fiber::suspend();
            }
        };

        $this->animations[] = $waitAnimation;
        return $this;
    }
}
