<?php

declare(strict_types=1);

namespace Phpanim\Animations;

use Fiber;

class Executor
{
    private array $animations = [];

    private ?Fiber $fiber = null;

    public static function withAnimations(array $animations): self
    {
        $instance = new self;
        $instance->animations = $animations;

        return $instance;
    }

    public function start()
    {
        if ($this->fiber !== null) {
            return $this;
        }

        $this->fiber = new Fiber(function () {
            foreach ($this->animations as $animation) {
                $animation();
            }
        });

        $this->fiber->start();
    }

    public function update()
    {
        if (! $this->fiber->isTerminated()) {
            $this->fiber->resume();
        }
    }
}
