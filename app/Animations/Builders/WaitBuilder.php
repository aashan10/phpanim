<?php

declare(strict_types=1);

namespace App\Animations\Builders;

use App\Animations\Builders\Traits\HasDuration;
use App\Animations\Builders\Traits\ResolvesProperties;
use App\Raylib\Raylib;
use Fiber;

class WaitBuilder extends Builder
{
    use HasDuration, ResolvesProperties;

    public function __construct(
        private AnimationBuilder|GroupBuilder $parent,
        private Raylib $raylib,
    )
    {}

    public function then(): BuilderInterface
    {
        return $this->parent->add($this);
    }

    public function build(): callable
    {
        $duration = $this->resolve($this->duration);

        return function () use ($duration) {

            $start = 0;
            while ($start < $duration) {
                $frameTime = $this->raylib->GetFrameTime();

                $start += $frameTime;
                Fiber::suspend();
            }

        };
    }

}
