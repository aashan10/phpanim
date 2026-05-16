<?php

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\GamepadButtonPressed;
use Phpanim\Events\KeyPressed;
use Phpanim\Input\Gamepad;
use Phpanim\Input\Keyboard;
use Phpanim\Raylib\Raylib;
use Phpanim\Scenes\SceneInterface;

class WelcomeScene implements SceneInterface
{
    private mixed $titleColor;

    private mixed $subtitleColor;

    private bool $isDone = false;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private GameState $gameState
    ) {}

    public function register(): void
    {
        $this->titleColor = $this->raylib->GetColor(0x00FF00FF); // Green
        $this->subtitleColor = $this->raylib->GetColor(0xFFFFFFFF); // White

        $this->dispatcher->on(Events::KeyPressed, function (KeyPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'welcome') {
                return;
            }

            if ($event->keyName === Keyboard::SPACE || $event->keyName === Keyboard::ENTER) {
                echo "Starting game...\n";
                $this->gameState->reset();
                $this->isDone = true;
            }
        });

        $this->dispatcher->on(Events::GamepadButtonPressed, function (GamepadButtonPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'welcome') {
                return;
            }

            if ($event->button === Gamepad::BUTTON_MIDDLE_RIGHT_ID || $event->button === Gamepad::BUTTON_RIGHT_FACE_DOWN_ID) { // Start or 'A' button
                $this->gameState->reset();
                $this->isDone = true;
            }
        });
    }

    public function isDone(): bool
    {
        return $this->isDone;
    }

    public function reset(): void
    {
        $this->isDone = false;
    }

    public function render(): void
    {

        // Center the text
        $screenWidth = 1600; // From default window width
        $screenHeight = 900; // From default window height

        $title = 'SNAKE GAME';
        $subtitle = 'Press SPACE or ENTER to start';
        $gamepadSubtitle = 'Press START or (A) to start';
        $instructions = 'Use arrow keys or WASD to control the snake';
        $gamepadInstructions = 'Use D-Pad or Left Stick to control the snake';

        // Calculate text widths for centering
        $titleWidth = strlen($title) * 24; // Approximate width calculation
        $subtitleWidth = strlen($subtitle) * 10;
        $gamepadSubtitleWidth = strlen($gamepadSubtitle) * 10;
        $instructionsWidth = strlen($instructions) * 10;
        $gamepadInstructionsWidth = strlen($gamepadInstructions) * 10;

        $this->raylib->DrawText(
            $title,
            (int) (($screenWidth - $titleWidth) / 2),
            (int) ($screenHeight / 2 - 120),
            48,
            $this->titleColor
        );

        $this->raylib->DrawText(
            $subtitle,
            (int) (($screenWidth - $subtitleWidth) / 2),
            (int) ($screenHeight / 2 - 40),
            20,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $gamepadSubtitle,
            (int) (($screenWidth - $gamepadSubtitleWidth) / 2),
            (int) ($screenHeight / 2 - 10),
            20,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $instructions,
            (int) (($screenWidth - $instructionsWidth) / 2),
            (int) ($screenHeight / 2 + 30),
            20,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $gamepadInstructions,
            (int) (($screenWidth - $gamepadInstructionsWidth) / 2),
            (int) ($screenHeight / 2 + 60),
            20,
            $this->subtitleColor
        );

        // Draw a simple snake illustration
        $startX = ($screenWidth / 2) - 60;
        $startY = ($screenHeight / 2) + 100;

        for ($i = 0; $i < 6; $i++) {
            $this->raylib->DrawRectangle(
                (int) ($startX + $i * 22),
                (int) $startY,
                20,
                20,
                $this->titleColor
            );
        }
    }
}
