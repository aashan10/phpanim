<?php

declare(strict_types=1);

namespace Aashan\Phpanim;

use Fiber;

class Scheduler 
{

    protected $animations = [];
    private bool $started = false;
    private bool $repeat  = false;
    private $easingFunc = null;

    private Fiber $fiber;

    public function __construct(private Raylib $rl, private object $target) {

    }

    public function withEasing(callable $easingFunc): self 
    {
        $this->easingFunc = $easingFunc;
        return $this;
    }

    public function tween(string $property, float $from, float $to, float $duration): self 
    {
        $this->animations[] = function () use($property, $from, $to, $duration) {

            $t = 0.0;

            while ($t < 1.0) {
                $elapsed = $this->rl->GetFrameTime();
                $t = min(1.0, ($t * $duration + $elapsed) / $duration);

                $value = $from + ($to - $from) * $this->applyEasing($t);
                $this->target->{$property} = $value;

                Fiber::suspend();
            }

        };

        return $this;
    }

    public function tweenMulti(float $duration, array $properties): self 
    {
        $this->animations[] = function () use ($properties, $duration) {

            $elapsed = 0.0;
            $t = 0.0;

            while ($t < 1.0) {
                $elapsed = $this->rl->GetFrameTime();
                $t = min(1.0, ($t * $duration + $elapsed) / $duration);

                $ease = $this->applyEasing($t);
                foreach ($properties as $property => [$from, $to]) {
                    $value = $from + ($to - $from) * $ease;
                    $this->target->{$property} = $value;
                }

                Fiber::suspend();
            }
        };

        return $this;
    }

    public function wait(float $duration): self
    {
        $this->animations[] = function () use($duration) {
            
            $elapsed = 0.0;

            while ($elapsed < $duration) {
                $elapsed += $this->rl->GetFrameTime();

                Fiber::suspend();
            }
        };
        return $this;
    }

    public function custom(callable $fn, float $duration): self
    {
        $this->animations[] = function () use ($fn, $duration) {

            $elapsed = 0.0;

            while ($elapsed < $duration) {
                $dt = $this->rl->GetFrameTime();
                $elapsed += $dt;
                $progress = min(1.0, $elapsed / $duration);

                $fn($this->target, $progress, $dt);

                Fiber::suspend();
            }
        };
        return $this;
    }

    public function parallel(array $schedulers): self 
    {
        $this->animations[] = function () use ($schedulers) {

            $schedulers = array_map(fn($s) => clone $s, $schedulers);

            foreach ($schedulers as $scheduler) {
                $scheduler->start();
            }

            $allFinished = false;
            while (!$allFinished) {
                $allFinished = true;
                foreach ($schedulers as $scheduler) {
                    if (!$scheduler->fiber->isTerminated()) {
                        $scheduler->fiber->resume();
                        $allFinished = false;
                    }
                }
                if (!$allFinished) {
                    Fiber::suspend();
                }
            }
        };
        return $this;
    }

    public function then(Scheduler $scheduler): self 
    {
        $this->animations[] = function () use ($scheduler) {
            $newScheduler = clone $scheduler;
            $newScheduler->start();

            while (!$newScheduler->fiber->isTerminated()) {
                $newScheduler->update();
                Fiber::suspend();
            }
        };
        return $this;
    }


    public function start() 
    {
        $this->fiber = new Fiber(function () {
            foreach ($this->animations as $animation) {
                $animation();
            }
        });

        $this->fiber->start();
        $this->started = true;
    }

    public function repeat(): self 
    {
        $this->repeat = true;
        return $this;
    }

    public function update() 
    {
        if (!$this->started) {
            $this->start();
        }

        if ($this->fiber->isSuspended()) {
            $this->fiber->resume();
        }

        if ($this->fiber->isTerminated() && $this->repeat) {
            $this->start();
        }
    }

    private function applyEasing(float $t): float 
    {
        if ($this->easingFunc) {
            return call_user_func($this->easingFunc, $t);
        }
        return $t;
    }

    public function __clone()
    {
        $this->fiber = new Fiber(function () {
            foreach ($this->animations as $animation) {
                $animation();
            }
        });
    }
}
