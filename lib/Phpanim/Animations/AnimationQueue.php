<?php

declare(strict_types=1);

namespace Phpanim\Animations;

use Fiber;

class AnimationQueue
{
    private array $queue = [];

    private array $runningAnimations = [];

    private array $interrupts = [];

    public function addAnimation(string $key, callable $animation, bool $interruptible = true): void
    {
        if ($interruptible && isset($this->runningAnimations[$key])) {
            $this->interrupts[$key] = true;
        }

        $this->queue[] = [
            'key' => $key,
            'animation' => $animation,
            'interruptible' => $interruptible,
        ];
    }

    public function addImmediateAnimation(string $key, callable $animation): void
    {
        // Stop any running animation with this key
        if (isset($this->runningAnimations[$key])) {
            $this->interrupts[$key] = true;
        }

        // Create and start fiber immediately
        $fiber = new Fiber($animation);
        $this->runningAnimations[$key] = $fiber;
        $fiber->start();
    }

    public function update(): void
    {
        // Process queued animations
        while (! empty($this->queue)) {
            $item = array_shift($this->queue);
            $key = $item['key'];

            // Skip if key is already running and not interruptible
            if (isset($this->runningAnimations[$key]) && ! $item['interruptible']) {
                continue;
            }

            // Start new animation
            $fiber = new Fiber($item['animation']);
            $this->runningAnimations[$key] = $fiber;
            $fiber->start();
        }

        // Update running animations
        foreach ($this->runningAnimations as $key => $fiber) {
            // Check for interrupts
            if (isset($this->interrupts[$key])) {
                unset($this->runningAnimations[$key]);
                unset($this->interrupts[$key]);

                continue;
            }

            if ($fiber->isSuspended()) {
                $fiber->resume();
            }

            if ($fiber->isTerminated()) {
                unset($this->runningAnimations[$key]);
            }
        }
    }

    public function isRunning(string $key): bool
    {
        return isset($this->runningAnimations[$key]);
    }

    public function interrupt(string $key): void
    {
        $this->interrupts[$key] = true;
    }

    public function clear(): void
    {
        $this->queue = [];
        $this->runningAnimations = [];
        $this->interrupts = [];
    }
}
