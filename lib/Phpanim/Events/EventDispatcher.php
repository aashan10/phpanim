<?php

declare(strict_types=1);

namespace Phpanim\Events;

class EventDispatcher
{
    private array $listeners = [];

    private array $oneTimeListeners = [];

    public function on(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function once(string $event, callable $listener): void
    {
        $this->oneTimeListeners[$event][] = $listener;
    }

    public function off(string $event, ?callable $listener = null): void
    {
        if ($listener === null) {
            unset($this->listeners[$event]);
            unset($this->oneTimeListeners[$event]);

            return;
        }

        if (isset($this->listeners[$event])) {
            $this->removeListener($this->listeners[$event], $listener);
        }

        if (isset($this->oneTimeListeners[$event])) {
            $this->removeListener($this->oneTimeListeners[$event], $listener);
        }
    }

    public function dispatch(string $event, mixed $data = null): void
    {
        // Execute persistent listeners
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener($data);
        }

        // Execute and remove one-time listeners
        if (isset($this->oneTimeListeners[$event])) {
            foreach ($this->oneTimeListeners[$event] as $listener) {
                $listener($data);
            }
            unset($this->oneTimeListeners[$event]);
        }
    }

    public function hasListeners(string $event): bool
    {
        return ! empty($this->listeners[$event]) || ! empty($this->oneTimeListeners[$event]);
    }

    private function removeListener(array &$listeners, callable $target): void
    {
        foreach ($listeners as $key => $listener) {
            if ($listener === $target) {
                unset($listeners[$key]);
                break;
            }
        }
    }
}
