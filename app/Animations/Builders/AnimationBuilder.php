<?php

declare(strict_types=1);

namespace App\Animations\Builders;

use App\Animations\Builders\Traits\HasTarget;
use App\Raylib\Raylib;


class AnimationBuilder extends Builder
{
    use HasTarget;

    protected array $animations = [];

    public function __construct(
        private Raylib $raylib
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
}
