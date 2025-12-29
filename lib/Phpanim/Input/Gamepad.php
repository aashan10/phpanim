<?php

declare(strict_types=1);

namespace Phpanim\Input;

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\GamepadButtonPressed;
use Phpanim\Events\GamepadAxisMoved;
use Phpanim\Raylib\Raylib;

class Gamepad
{
    public const BUTTON_UNKNOWN = 'UNKNOWN';
    public const BUTTON_LEFT_FACE_UP = 'LEFT_FACE_UP';
    public const BUTTON_LEFT_FACE_RIGHT = 'LEFT_FACE_RIGHT';
    public const BUTTON_LEFT_FACE_DOWN = 'LEFT_FACE_DOWN';
    public const BUTTON_LEFT_FACE_LEFT = 'LEFT_FACE_LEFT';
    public const BUTTON_RIGHT_FACE_UP = 'RIGHT_FACE_UP';
    public const BUTTON_RIGHT_FACE_RIGHT = 'RIGHT_FACE_RIGHT';
    public const BUTTON_RIGHT_FACE_DOWN = 'RIGHT_FACE_DOWN';
    public const BUTTON_RIGHT_FACE_LEFT = 'RIGHT_FACE_LEFT';
    public const BUTTON_LEFT_TRIGGER_1 = 'LEFT_TRIGGER_1';
    public const BUTTON_LEFT_TRIGGER_2 = 'LEFT_TRIGGER_2';
    public const BUTTON_RIGHT_TRIGGER_1 = 'RIGHT_TRIGGER_1';
    public const BUTTON_RIGHT_TRIGGER_2 = 'RIGHT_TRIGGER_2';
    public const BUTTON_MIDDLE_LEFT = 'MIDDLE_LEFT';
    public const BUTTON_MIDDLE = 'MIDDLE';
    public const BUTTON_MIDDLE_RIGHT = 'MIDDLE_RIGHT';
    public const BUTTON_LEFT_THUMB = 'LEFT_THUMB';
    public const BUTTON_RIGHT_THUMB = 'RIGHT_THUMB';

    public const AXIS_LEFT_X = 'LEFT_X';
    public const AXIS_LEFT_Y = 'LEFT_Y';
    public const AXIS_RIGHT_X = 'RIGHT_X';
    public const AXIS_RIGHT_Y = 'RIGHT_Y';
    public const AXIS_LEFT_TRIGGER = 'LEFT_TRIGGER';
    public const AXIS_RIGHT_TRIGGER = 'RIGHT_TRIGGER';

    private array $previousButtonStates = [];
    private array $previousAxisValues = [];
    private float $deadZone = 0.1;
    
    private array $buttonNames = [
        0 => self::BUTTON_UNKNOWN,
        1 => self::BUTTON_LEFT_FACE_UP,
        2 => self::BUTTON_LEFT_FACE_RIGHT,
        3 => self::BUTTON_LEFT_FACE_DOWN,
        4 => self::BUTTON_LEFT_FACE_LEFT,
        5 => self::BUTTON_RIGHT_FACE_UP,
        6 => self::BUTTON_RIGHT_FACE_RIGHT,
        7 => self::BUTTON_RIGHT_FACE_DOWN,
        8 => self::BUTTON_RIGHT_FACE_LEFT,
        9 => self::BUTTON_LEFT_TRIGGER_1,
        10 => self::BUTTON_LEFT_TRIGGER_2,
        11 => self::BUTTON_RIGHT_TRIGGER_1,
        12 => self::BUTTON_RIGHT_TRIGGER_2,
        13 => self::BUTTON_MIDDLE_LEFT,
        14 => self::BUTTON_MIDDLE,
        15 => self::BUTTON_MIDDLE_RIGHT,
        16 => self::BUTTON_LEFT_THUMB,
        17 => self::BUTTON_RIGHT_THUMB
    ];

    private array $axisNames = [
        0 => self::AXIS_LEFT_X,
        1 => self::AXIS_LEFT_Y,
        2 => self::AXIS_RIGHT_X,
        3 => self::AXIS_RIGHT_Y,
        4 => self::AXIS_LEFT_TRIGGER,
        5 => self::AXIS_RIGHT_TRIGGER
    ];

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher
    ) {}

    public function poll(): void
    {
        $timestamp = microtime(true);

        // Check all possible gamepads (0-3)
        for ($gamepad = 0; $gamepad < 4; $gamepad++) {
            if (!$this->raylib->IsGamepadAvailable($gamepad)) {
                continue;
            }

            // Check buttons
            foreach ($this->buttonNames as $button => $buttonName) {
                $isPressed = $this->raylib->IsGamepadButtonDown($gamepad, $button);
                $wasPressed = $this->previousButtonStates[$gamepad][$button] ?? false;

                if ($isPressed && !$wasPressed) {
                    $this->dispatcher->dispatch('gamepad.button.pressed', new GamepadButtonPressed(
                        $gamepad,
                        $button,
                        $buttonName,
                        $timestamp
                    ));
                }

                $this->previousButtonStates[$gamepad][$button] = $isPressed;
            }

            // Check axes
            foreach ($this->axisNames as $axis => $axisName) {
                $value = $this->raylib->GetGamepadAxisMovement($gamepad, $axis);
                
                // Apply deadzone
                if (abs($value) < $this->deadZone) {
                    $value = 0.0;
                }

                $previousValue = $this->previousAxisValues[$gamepad][$axis] ?? 0.0;

                if (abs($value - $previousValue) > 0.001) { // Small threshold for float comparison
                    $this->dispatcher->dispatch('gamepad.axis.moved', new GamepadAxisMoved(
                        $gamepad,
                        $axis,
                        $value,
                        $timestamp
                    ));
                }

                $this->previousAxisValues[$gamepad][$axis] = $value;
            }
        }
    }

    public function isGamepadAvailable(int $gamepad): bool
    {
        return $this->raylib->IsGamepadAvailable($gamepad);
    }

    public function getGamepadName(int $gamepad): string
    {
        return $this->raylib->GetGamepadName($gamepad);
    }

    public function isButtonDown(int $gamepad, int $button): bool
    {
        return $this->raylib->IsGamepadButtonDown($gamepad, $button);
    }

    public function getAxisMovement(int $gamepad, int $axis): float
    {
        $value = $this->raylib->GetGamepadAxisMovement($gamepad, $axis);
        return abs($value) < $this->deadZone ? 0.0 : $value;
    }

    public function setDeadZone(float $deadZone): void
    {
        $this->deadZone = max(0.0, min(1.0, $deadZone));
    }

    public function getButtonName(int $button): string
    {
        return $this->buttonNames[$button] ?? 'UNKNOWN';
    }

    public function getAxisName(int $axis): string
    {
        return $this->axisNames[$axis] ?? 'UNKNOWN';
    }
}