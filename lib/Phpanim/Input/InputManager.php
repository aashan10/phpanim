<?php

declare(strict_types=1);

namespace Phpanim\Input;

use Phpanim\Events\EventDispatcher;
use Phpanim\Raylib\Raylib;

class InputManager
{
    public readonly Keyboard $keyboard;
    public readonly Mouse $mouse;
    public readonly Gamepad $gamepad;

    public function __construct(
        Raylib $raylib,
        public readonly EventDispatcher $dispatcher
    ) {
        $this->keyboard = new Keyboard($raylib, $dispatcher);
        $this->mouse = new Mouse($raylib, $dispatcher);
        $this->gamepad = new Gamepad($raylib, $dispatcher);
    }

    public function poll(): void
    {
        $this->keyboard->poll();
        $this->mouse->poll();
        $this->gamepad->poll();
    }

    public function on(string $event, callable $listener): void
    {
        $this->dispatcher->on($event, $listener);
    }

    public function once(string $event, callable $listener): void
    {
        $this->dispatcher->once($event, $listener);
    }

    public function off(string $event, ?callable $listener = null): void
    {
        $this->dispatcher->off($event, $listener);
    }
}