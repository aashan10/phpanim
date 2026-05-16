<?php

declare(strict_types=1);

namespace Phpanim\Input;

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\GamepadAxisMoved;
use Phpanim\Events\GamepadButtonPressed;
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

    public const AXIS_UNKNOWN = 'UNKNOWN';

    // Button IDs
    public const BUTTON_UNKNOWN_ID = 0;

    public const BUTTON_LEFT_FACE_UP_ID = 1;

    public const BUTTON_LEFT_FACE_RIGHT_ID = 2;

    public const BUTTON_LEFT_FACE_DOWN_ID = 3;

    public const BUTTON_LEFT_FACE_LEFT_ID = 4;

    public const BUTTON_RIGHT_FACE_UP_ID = 5;

    public const BUTTON_RIGHT_FACE_RIGHT_ID = 6;

    public const BUTTON_RIGHT_FACE_DOWN_ID = 7;

    public const BUTTON_RIGHT_FACE_LEFT_ID = 8;

    public const BUTTON_LEFT_TRIGGER_1_ID = 9;

    public const BUTTON_LEFT_TRIGGER_2_ID = 10;

    public const BUTTON_RIGHT_TRIGGER_1_ID = 11;

    public const BUTTON_RIGHT_TRIGGER_2_ID = 12;

    public const BUTTON_MIDDLE_LEFT_ID = 13;

    public const BUTTON_MIDDLE_ID = 14;

    public const BUTTON_MIDDLE_RIGHT_ID = 15;

    public const BUTTON_LEFT_THUMB_ID = 16;

    public const BUTTON_RIGHT_THUMB_ID = 17;

    // Axis IDs
    public const AXIS_LEFT_X_ID = 0;

    public const AXIS_LEFT_Y_ID = 1;

    public const AXIS_RIGHT_X_ID = 2;

    public const AXIS_RIGHT_Y_ID = 3;

    public const AXIS_LEFT_TRIGGER_ID = 4;

    public const AXIS_RIGHT_TRIGGER_ID = 5;

    private array $previousButtonStates = [];

    private array $previousAxisValues = [];

    private float $deadZone = 0.1;

    private array $buttonNames = [
        self::BUTTON_UNKNOWN_ID => self::BUTTON_UNKNOWN,
        self::BUTTON_LEFT_FACE_UP_ID => self::BUTTON_LEFT_FACE_UP,
        self::BUTTON_LEFT_FACE_RIGHT_ID => self::BUTTON_LEFT_FACE_RIGHT,
        self::BUTTON_LEFT_FACE_DOWN_ID => self::BUTTON_LEFT_FACE_DOWN,
        self::BUTTON_LEFT_FACE_LEFT_ID => self::BUTTON_LEFT_FACE_LEFT,
        self::BUTTON_RIGHT_FACE_UP_ID => self::BUTTON_RIGHT_FACE_UP,
        self::BUTTON_RIGHT_FACE_RIGHT_ID => self::BUTTON_RIGHT_FACE_RIGHT,
        self::BUTTON_RIGHT_FACE_DOWN_ID => self::BUTTON_RIGHT_FACE_DOWN,
        self::BUTTON_RIGHT_FACE_LEFT_ID => self::BUTTON_RIGHT_FACE_LEFT,
        self::BUTTON_LEFT_TRIGGER_1_ID => self::BUTTON_LEFT_TRIGGER_1,
        self::BUTTON_LEFT_TRIGGER_2_ID => self::BUTTON_LEFT_TRIGGER_2,
        self::BUTTON_RIGHT_TRIGGER_1_ID => self::BUTTON_RIGHT_TRIGGER_1,
        self::BUTTON_RIGHT_TRIGGER_2_ID => self::BUTTON_RIGHT_TRIGGER_2,
        self::BUTTON_MIDDLE_LEFT_ID => self::BUTTON_MIDDLE_LEFT,
        self::BUTTON_MIDDLE_ID => self::BUTTON_MIDDLE,
        self::BUTTON_MIDDLE_RIGHT_ID => self::BUTTON_MIDDLE_RIGHT,
        self::BUTTON_LEFT_THUMB_ID => self::BUTTON_LEFT_THUMB,
        self::BUTTON_RIGHT_THUMB_ID => self::BUTTON_RIGHT_THUMB,
    ];

    private array $axisNames = [
        self::AXIS_LEFT_X_ID => self::AXIS_LEFT_X,
        self::AXIS_LEFT_Y_ID => self::AXIS_LEFT_Y,
        self::AXIS_RIGHT_X_ID => self::AXIS_RIGHT_X,
        self::AXIS_RIGHT_Y_ID => self::AXIS_RIGHT_Y,
        self::AXIS_LEFT_TRIGGER_ID => self::AXIS_LEFT_TRIGGER,
        self::AXIS_RIGHT_TRIGGER_ID => self::AXIS_RIGHT_TRIGGER,
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
            if (! $this->raylib->IsGamepadAvailable($gamepad)) {
                continue;
            }

            // Check buttons
            foreach (array_keys($this->buttonNames) as $button) {
                // Skip BUTTON_UNKNOWN_ID as it's not a real button to check
                if ($button === self::BUTTON_UNKNOWN_ID) {
                    continue;
                }
                $isPressed = $this->raylib->IsGamepadButtonDown($gamepad, $button);
                $wasPressed = $this->previousButtonStates[$gamepad][$button] ?? false;

                if ($isPressed && ! $wasPressed) {
                    $this->dispatcher->dispatch('gamepad.button.pressed', new GamepadButtonPressed(
                        $gamepad,
                        $button,
                        $this->buttonNames[$button],
                        $timestamp
                    ));
                }

                $this->previousButtonStates[$gamepad][$button] = $isPressed;
            }

            // Check axes
            foreach (array_keys($this->axisNames) as $axis) {
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
        return $this->buttonNames[$button] ?? self::BUTTON_UNKNOWN;
    }

    public function getAxisName(int $axis): string
    {
        return $this->axisNames[$axis] ?? self::AXIS_UNKNOWN;
    }
}
