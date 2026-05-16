<?php

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\GamepadButtonPressed;
use Phpanim\Events\KeyPressed;
use Phpanim\Input\Gamepad;
use Phpanim\Input\Keyboard;
use Phpanim\Raylib\Raylib;
use Phpanim\Scenes\SceneInterface;

class GameOverScene implements SceneInterface
{
    private mixed $gameOverColor;

    private mixed $scoreColor;

    private mixed $subtitleColor;

    private mixed $dinoColor;

    private bool $isDone = false;

    private float $flashTimer = 0.0;

    private bool $showGameOver = true;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private DinoGameState $gameState
    ) {}

    public function register(): void
    {
        $this->gameOverColor = $this->raylib->GetColor(0x535353FF);  // Dark gray
        $this->scoreColor = $this->raylib->GetColor(0x000000FF);     // Black
        $this->subtitleColor = $this->raylib->GetColor(0x535353FF);  // Dark gray
        $this->dinoColor = $this->raylib->GetColor(0x535353FF);      // Dark gray

        $this->dispatcher->on(Events::KeyPressed, function (KeyPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'gameOver') {
                return;
            }

            if ($event->keyName === Keyboard::SPACE ||
                $event->keyName === Keyboard::ENTER ||
                $event->keyName === Keyboard::UP) {
                $this->gameState->reset();
                $this->isDone = true;
            } elseif ($event->keyName === Keyboard::ESCAPE) {
                $this->gameState->transitionTo('welcome');
                $this->isDone = true;
            } elseif ($event->keyName === Keyboard::R) {
                $this->gameState->reload();
                $this->isDone = true;
            }
        });

        $this->dispatcher->on(Events::GamepadButtonPressed, function (GamepadButtonPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'gameOver') {
                return;
            }

            if ($event->button === Gamepad::BUTTON_RIGHT_FACE_DOWN_ID || // A button
                $event->button === Gamepad::BUTTON_MIDDLE_RIGHT_ID ||    // Start button
                $event->button === Gamepad::BUTTON_LEFT_FACE_UP_ID) {    // D-pad up
                $this->gameState->reset();
                $this->isDone = true;
            } elseif ($event->button === Gamepad::BUTTON_MIDDLE_LEFT_ID) { // Select/Back button
                $this->gameState->transitionTo('welcome');
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
        $this->flashTimer = 0.0;
        $this->showGameOver = true;
    }

    public function render(): void
    {
        // Update flash timer for "GAME OVER" text
        $this->flashTimer += $this->raylib->GetFrameTime();
        if ($this->flashTimer >= 0.5) {
            $this->showGameOver = ! $this->showGameOver;
            $this->flashTimer = 0.0;
        }

        $screenWidth = 1600;
        $screenHeight = 900;

        // Draw ground line
        $groundY = DinoGameState::GROUND_Y + 40;
        $this->raylib->DrawLine(0, (int) $groundY, $screenWidth, (int) $groundY, $this->gameOverColor);

        // Draw crashed/dead dino
        $dinoX = 200;
        $dinoY = DinoGameState::GROUND_Y;

        // Dino body (lying down/crashed)
        $this->raylib->DrawRectangle(
            (int) $dinoX,
            (int) $dinoY + 20,
            DinoGameState::DINO_WIDTH + 10,
            DinoGameState::DINO_HEIGHT - 15,
            $this->dinoColor
        );

        // Draw "X" eyes to show it's dead
        $this->raylib->DrawText('X', (int) $dinoX + 5, (int) $dinoY + 22, 12, $this->raylib->GetColor(0xFF0000FF));
        $this->raylib->DrawText('X', (int) $dinoX + 20, (int) $dinoY + 22, 12, $this->raylib->GetColor(0xFF0000FF));

        // Draw a cactus nearby (the one that caused the collision)
        $cactusX = $dinoX + 60;
        $cactusY = DinoGameState::GROUND_Y;
        $this->raylib->DrawRectangle(
            (int) $cactusX,
            (int) $cactusY,
            DinoGameState::CACTUS_WIDTH,
            DinoGameState::CACTUS_HEIGHT,
            $this->raylib->GetColor(0x00AA00FF)
        );

        // Draw "GAME OVER" text (flashing)
        if ($this->showGameOver) {
            $gameOverText = 'G A M E   O V E R';
            $gameOverWidth = strlen($gameOverText) * 24;
            $this->raylib->DrawText(
                $gameOverText,
                (int) (($screenWidth - $gameOverWidth) / 2),
                300,
                48,
                $this->gameOverColor
            );
        }

        // Draw scores
        $currentScoreText = 'SCORE: '.str_pad((string) $this->gameState->score, 5, '0', STR_PAD_LEFT);
        $currentScoreWidth = strlen($currentScoreText) * 12;
        $this->raylib->DrawText(
            $currentScoreText,
            (int) (($screenWidth - $currentScoreWidth) / 2),
            400,
            24,
            $this->scoreColor
        );

        // Show high score if it's a new record
        if ($this->gameState->score >= $this->gameState->highScore) {
            $newRecordText = 'NEW HIGH SCORE!';
            $newRecordWidth = strlen($newRecordText) * 10;
            $this->raylib->DrawText(
                $newRecordText,
                (int) (($screenWidth - $newRecordWidth) / 2),
                450,
                20,
                $this->raylib->GetColor(0xFF4444FF) // Red
            );
        } else {
            $highScoreText = 'HIGH: '.str_pad((string) $this->gameState->highScore, 5, '0', STR_PAD_LEFT);
            $highScoreWidth = strlen($highScoreText) * 12;
            $this->raylib->DrawText(
                $highScoreText,
                (int) (($screenWidth - $highScoreWidth) / 2),
                450,
                24,
                $this->scoreColor
            );
        }

        // Draw restart instructions
        $restartText = 'Press SPACE, ENTER, or UP to play again';
        $restartWidth = strlen($restartText) * 10;
        $this->raylib->DrawText(
            $restartText,
            (int) (($screenWidth - $restartWidth) / 2),
            550,
            20,
            $this->subtitleColor
        );

        $gamepadRestartText = 'Press (A), START, or UP on gamepad';
        $gamepadRestartWidth = strlen($gamepadRestartText) * 10;
        $this->raylib->DrawText(
            $gamepadRestartText,
            (int) (($screenWidth - $gamepadRestartWidth) / 2),
            580,
            20,
            $this->subtitleColor
        );

        $menuText = 'Press ESC or SELECT to return to menu';
        $menuWidth = strlen($menuText) * 10;
        $this->raylib->DrawText(
            $menuText,
            (int) (($screenWidth - $menuWidth) / 2),
            610,
            20,
            $this->subtitleColor
        );

        $reloadText = 'Press R to restart from welcome';
        $reloadWidth = strlen($reloadText) * 10;
        $this->raylib->DrawText(
            $reloadText,
            (int) (($screenWidth - $reloadWidth) / 2),
            640,
            20,
            $this->subtitleColor
        );

        // Draw some dust clouds around the crash site
        $this->drawDustCloud($dinoX - 20, $dinoY + 30);
        $this->drawDustCloud($dinoX + 50, $dinoY + 35);
        $this->drawDustCloud($cactusX + 30, $cactusY + 25);
    }

    private function drawDustCloud(float $x, float $y): void
    {
        $dustColor = $this->raylib->GetColor(0xCCCCCCFF); // Light gray dust

        // Small particles
        for ($i = 0; $i < 5; $i++) {
            $offsetX = rand(-10, 10);
            $offsetY = rand(-5, 5);
            $this->raylib->DrawRectangle(
                (int) $x + $offsetX,
                (int) $y + $offsetY,
                2,
                2,
                $dustColor
            );
        }
    }
}
