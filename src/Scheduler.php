<?php

declare(strict_types=1);

namespace Aashan\Phpanim;

use FFI\CData;
use Fiber;

class Scheduler
{
    protected $animations = [];
    private bool $started = false;
    private bool $repeat = false;
    private $easingFunc = null;

    private Fiber $fiber;

    public function __construct(
        private Raylib $rl,
        private object $target,
    ) {}

    private function readProperty(string $path): mixed
    {
        $parts = explode('.', $path);
        $value = $this->target;

        foreach ($parts as $part) {
            if (is_object($value) && property_exists($value, $part)) {
                $value = $value->{$part};
            } else {
                throw new \RuntimeException("Property '{$path}' does not exist on target object.");
            }
        }

        return $value;
    }

    private function writeProperty(string $path, mixed $newValue): void
    {
        $parts = explode('.', $path);
        $value = &$this->target;

        foreach ($parts as $part) {
            if (is_object($value) && property_exists($value, $part)) {
                $value = &$value->{$part};
            } else {
                throw new \RuntimeException("Property '{$path}' does not exist on target object.");
            }
        }

        $value = $newValue;
    }

    public function new(): self
    {
        return new self($this->rl, $this->target);
    }

    public function withEasing(callable $easingFunc): self
    {
        $this->easingFunc = $easingFunc;
        return $this;
    }

    public function tween(string $property, float $from, float $to, float $duration): self
    {
        $this->animations[] = function () use ($property, $from, $to, $duration) {
            $t = 0.0;

            while ($t < 1.0) {
                $elapsed = $this->rl->GetFrameTime();
                $t = min(1.0, (($t * $duration) + $elapsed) / $duration);

                $value = $from + (($to - $from) * $this->applyEasing($t));
                $this->writeProperty($property, $value);
                Fiber::suspend();
            }
        };

        return $this;
    }

    public function rotate(CData $point, float $deg, float $duration, null|CData $origin = null): self
    {
        $this->animations[] = function () use ($point, $deg, $duration, $origin) {
            $startX = $point->x;
            $startY = $point->y;

            $angleRad = deg2rad($deg);
            $cosAngle = cos($angleRad);
            $sinAngle = sin($angleRad);

            $originX = $origin ? $origin->x : 0.0;
            $originY = $origin ? $origin->y : 0.0;

            $t = 0.0;

            while ($t < 1.0) {
                $elapsed = $this->rl->GetFrameTime();
                $t = min(1.0, (($t * $duration) + $elapsed) / $duration);

                $ease = $this->applyEasing($t);
                $currentAngle = $angleRad * $ease;

                $rotatedX =
                    (cos($currentAngle) * ($startX - $originX)) - (sin($currentAngle) * ($startY - $originY))
                    + $originX;
                $rotatedY =
                    (sin($currentAngle) * ($startX - $originX))
                    + (cos($currentAngle) * ($startY - $originY))
                    + $originY;

                $point->x = $rotatedX;
                $point->y = $rotatedY;

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
                $t = min(1.0, (($t * $duration) + $elapsed) / $duration);

                $ease = $this->applyEasing($t);
                foreach ($properties as $property => [$from, $to]) {
                    $value = $from + (($to - $from) * $ease);
                    $this->writeProperty($property, $value);
                }

                Fiber::suspend();
            }
        };

        return $this;
    }

    public function wait(float $duration): self
    {
        $this->animations[] = function () use ($duration) {
            $elapsed = 0.0;

            while ($elapsed < $duration) {
                $elapsed += $this->rl->GetFrameTime();

                Fiber::suspend();
            }
        };
        return $this;
    }

    public function manual(callable $fn): self
    {
        $this->animations[] = $fn;
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
        $this->animations[] = static function () use ($schedulers) {
            $schedulers = array_map(static fn($s) => clone $s, $schedulers);

            foreach ($schedulers as $scheduler) {
                $scheduler->start();
            }

            $allFinished = false;
            while (!$allFinished) {
                $allFinished = true;
                foreach ($schedulers as $scheduler) {
                    if ($scheduler->fiber->isTerminated()) {
                        continue;
                    }

                    $scheduler->fiber->resume();
                    $allFinished = false;
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
        $this->animations[] = static function () use ($scheduler) {
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
