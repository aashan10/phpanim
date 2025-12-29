<?php

declare(strict_types=1);

namespace Phpanim\Input;

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\MouseClicked;
use Phpanim\Events\MouseReleased;
use Phpanim\Events\MouseMoved;
use Phpanim\Events\MouseScrolled;
use Phpanim\Raylib\Raylib;

class Mouse
{
    public const LEFT = 'LEFT';
    public const RIGHT = 'RIGHT';
    public const MIDDLE = 'MIDDLE';
    public const SIDE = 'SIDE';
    public const EXTRA = 'EXTRA';
    public const FORWARD = 'FORWARD';
    public const BACK = 'BACK';

    private array $previousButtonState = [];
    private float $previousX = 0;
    private float $previousY = 0;
    private array $buttonNames = [
        0 => self::LEFT,
        1 => self::RIGHT,
        2 => self::MIDDLE,
        3 => self::SIDE,
        4 => self::EXTRA,
        5 => self::FORWARD,
        6 => self::BACK
    ];

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher
    ) {}

    public function poll(): void
    {
        $timestamp = microtime(true);

        // Check mouse buttons
        foreach ($this->buttonNames as $button => $buttonName) {
            $isPressed = $this->raylib->IsMouseButtonDown($button);
            $wasPressed = $this->previousButtonState[$button] ?? false;

            if ($isPressed && !$wasPressed) {
                $this->dispatcher->dispatch('mouse.clicked', new MouseClicked(
                    (float)$this->raylib->GetMouseX(),
                    (float)$this->raylib->GetMouseY(),
                    $button,
                    $timestamp
                ));
            } elseif (!$isPressed && $wasPressed) {
                $this->dispatcher->dispatch('mouse.released', new MouseReleased(
                    (float)$this->raylib->GetMouseX(),
                    (float)$this->raylib->GetMouseY(),
                    $button,
                    $timestamp
                ));
            }

            $this->previousButtonState[$button] = $isPressed;
        }

        // Check mouse movement
        $currentX = (float)$this->raylib->GetMouseX();
        $currentY = (float)$this->raylib->GetMouseY();

        if ($currentX !== $this->previousX || $currentY !== $this->previousY) {
            $deltaX = $currentX - $this->previousX;
            $deltaY = $currentY - $this->previousY;

            $this->dispatcher->dispatch('mouse.moved', new MouseMoved(
                $currentX,
                $currentY,
                $deltaX,
                $deltaY,
                $timestamp
            ));

            $this->previousX = $currentX;
            $this->previousY = $currentY;
        }

        // Check mouse wheel
        $wheelX = (float)$this->raylib->GetMouseWheelMoveV()->x ?? 0.0;
        $wheelY = (float)$this->raylib->GetMouseWheelMoveV()->y ?? 0.0;

        if ($wheelX !== 0.0 || $wheelY !== 0.0) {
            $this->dispatcher->dispatch('mouse.scrolled', new MouseScrolled(
                $wheelX,
                $wheelY,
                $timestamp
            ));
        }
    }

    public function getPosition(): array
    {
        return [(float)$this->raylib->GetMouseX(), (float)$this->raylib->GetMouseY()];
    }

    public function isButtonDown(int $button): bool
    {
        return (bool)$this->raylib->IsMouseButtonDown($button);
    }

    public function isButtonPressed(int $button): bool
    {
        return (bool)$this->raylib->IsMouseButtonPressed($button);
    }

    public function getButtonName(int $button): string
    {
        return $this->buttonNames[$button] ?? 'UNKNOWN';
    }
}
