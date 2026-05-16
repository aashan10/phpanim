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

    private mixed $dinoColor;

    private bool $isDone = false;

    private float $blinkTime = 0.0;

    private bool $showStartText = true;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private DinoGameState $gameState
    ) {}

    public function register(): void
    {
        $this->titleColor = $this->raylib->GetColor(0x535353FF);   // Dark gray
        $this->subtitleColor = $this->raylib->GetColor(0x000000FF); // Black
        $this->dinoColor = $this->raylib->GetColor(0x535353FF);     // Dark gray

        $this->dispatcher->on(Events::KeyPressed, function (KeyPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'welcome') {
                return;
            }

            if ($event->keyName === Keyboard::SPACE ||
                $event->keyName === Keyboard::ENTER ||
                $event->keyName === Keyboard::UP) {
                $this->gameState->reset();
                $this->isDone = true;
            }
        });

        $this->dispatcher->on(Events::GamepadButtonPressed, function (GamepadButtonPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'welcome') {
                return;
            }

            if ($event->button === Gamepad::BUTTON_MIDDLE_RIGHT_ID ||
                $event->button === Gamepad::BUTTON_RIGHT_FACE_DOWN_ID ||
                $event->button === Gamepad::BUTTON_LEFT_FACE_UP_ID) {
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
        $this->blinkTime = 0.0;
        $this->showStartText = true;
    }

    public function render(): void
    {
        // Update blink timer
        $this->blinkTime += $this->raylib->GetFrameTime();
        if ($this->blinkTime >= 0.5) {
            $this->showStartText = ! $this->showStartText;
            $this->blinkTime = 0.0;
        }

        $screenWidth = 1600;
        $screenHeight = 900;

        // Draw ground line
        $this->raylib->DrawLine(0, (int) DinoGameState::GROUND_Y + 40, $screenWidth, (int) DinoGameState::GROUND_Y + 40, $this->titleColor);

        // Draw title
        $titleText = 'CHROME DINO';
        $titleWidth = strlen($titleText) * 24;
        $this->raylib->DrawText(
            $titleText,
            (int) (($screenWidth - $titleWidth) / 2),
            200,
            48,
            $this->titleColor
        );

        // Draw dino sprite (simple rectangle representation)
        $dinoX = ($screenWidth / 2) - 100;
        $dinoY = DinoGameState::GROUND_Y;

        // Dino body
        $this->raylib->DrawRectangle(
            (int) $dinoX,
            (int) $dinoY,
            DinoGameState::DINO_WIDTH,
            DinoGameState::DINO_HEIGHT,
            $this->dinoColor
        );

        // Dino eye
        $this->raylib->DrawRectangle(
            (int) $dinoX + 25,
            (int) $dinoY + 8,
            4,
            4,
            $this->raylib->GetColor(0x000000FF)
        );

        // Draw a simple cactus
        $cactusX = ($screenWidth / 2) + 100;
        $cactusY = DinoGameState::GROUND_Y;

        $this->raylib->DrawRectangle(
            (int) $cactusX,
            (int) $cactusY,
            DinoGameState::CACTUS_WIDTH,
            DinoGameState::CACTUS_HEIGHT,
            $this->raylib->GetColor(0x00AA00FF) // Green
        );

        // Draw clouds
        $this->drawCloud(200, 150);
        $this->drawCloud(400, 180);
        $this->drawCloud(1200, 160);

        // Draw instructions
        if ($this->showStartText) {
            $startText = 'Press SPACE, ENTER, or UP to start';
            $startWidth = strlen($startText) * 10;
            $this->raylib->DrawText(
                $startText,
                (int) (($screenWidth - $startWidth) / 2),
                500,
                20,
                $this->subtitleColor
            );

            $gamepadText = 'Press START, (A), or UP on gamepad';
            $gamepadWidth = strlen($gamepadText) * 10;
            $this->raylib->DrawText(
                $gamepadText,
                (int) (($screenWidth - $gamepadWidth) / 2),
                530,
                20,
                $this->subtitleColor
            );
        }

        $instructionText = 'Use SPACE or UP to jump, DOWN to duck';
        $instructionWidth = strlen($instructionText) * 10;
        $this->raylib->DrawText(
            $instructionText,
            (int) (($screenWidth - $instructionWidth) / 2),
            600,
            20,
            $this->subtitleColor
        );

        // Show high score if available
        if ($this->gameState->highScore > 0) {
            $highScoreText = 'HI '.str_pad((string) $this->gameState->highScore, 5, '0', STR_PAD_LEFT);
            $this->raylib->DrawText(
                $highScoreText,
                $screenWidth - 150,
                50,
                20,
                $this->subtitleColor
            );
        }
    }

    private function drawCloud(float $x, float $y): void
    {
        $cloudColor = $this->raylib->GetColor(0xF0F0F0FF); // Light gray

        // Simple cloud made of circles/rectangles
        $this->raylib->DrawRectangle((int) $x, (int) $y + 10, 40, 20, $cloudColor);
        $this->raylib->DrawRectangle((int) $x + 10, (int) $y, 20, 20, $cloudColor);
        $this->raylib->DrawRectangle((int) $x + 20, (int) $y + 15, 30, 15, $cloudColor);
    }
}
