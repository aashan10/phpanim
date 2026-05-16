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
        $this->titleColor = $this->raylib->GetColor(0xFF0000FF);   // Red
        $this->subtitleColor = $this->raylib->GetColor(0xFFFFFFFF); // White

        $this->dispatcher->on(Events::KeyPressed, function (KeyPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'gameOver') {
                return;
            }

            if ($event->keyName === Keyboard::SPACE || $event->keyName === Keyboard::ENTER) {
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

            if ($event->button === Gamepad::BUTTON_RIGHT_FACE_DOWN_ID) { // 'A' button on Xbox controller
                $this->gameState->reset();
                $this->isDone = true;
            } elseif ($event->button === Gamepad::BUTTON_MIDDLE_LEFT_ID) { // 'Select' or 'Back' button
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
    }

    public function render(): void
    {

        // Center the text
        $screenWidth = 1600;
        $screenHeight = 900;

        $gameOverText = 'GAME OVER';
        $finalScoreText = 'Final Score: '.$this->gameState->score;
        $restartText = 'Press SPACE or ENTER to play again';
        $menuText = 'Press ESC to return to menu';
        $reloadText = 'Press R to reload from welcome screen';
        $gamepadRestartText = 'Press (A) to play again';
        $gamepadMenuText = 'Press SELECT to return to menu';

        // Calculate text widths for centering (approximate)
        $gameOverWidth = strlen($gameOverText) * 28;
        $finalScoreWidth = strlen($finalScoreText) * 12;
        $restartWidth = strlen($restartText) * 10;
        $menuWidth = strlen($menuText) * 10;
        $reloadWidth = strlen($reloadText) * 10;
        $gamepadRestartWidth = strlen($gamepadRestartText) * 10;
        $gamepadMenuWidth = strlen($gamepadMenuText) * 10;

        $this->raylib->DrawText(
            $gameOverText,
            ($screenWidth - $gameOverWidth) / 2,
            $screenHeight / 2 - 120,
            56,
            $this->titleColor
        );

        $this->raylib->DrawText(
            $finalScoreText,
            ($screenWidth - $finalScoreWidth) / 2,
            $screenHeight / 2 - 40,
            24,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $restartText,
            ($screenWidth - $restartWidth) / 2,
            $screenHeight / 2 + 20,
            20,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $menuText,
            ($screenWidth - $menuWidth) / 2,
            $screenHeight / 2 + 50,
            20,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $reloadText,
            ($screenWidth - $reloadWidth) / 2,
            $screenHeight / 2 + 80,
            20,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $gamepadRestartText,
            ($screenWidth - $gamepadRestartWidth) / 2,
            $screenHeight / 2 + 110,
            20,
            $this->subtitleColor
        );

        $this->raylib->DrawText(
            $gamepadMenuText,
            ($screenWidth - $gamepadMenuWidth) / 2,
            $screenHeight / 2 + 140,
            20,
            $this->subtitleColor
        );

        // Draw a sad snake illustration
        $startX = ($screenWidth / 2) - 40;
        $startY = ($screenHeight / 2) + 150;

        // Snake body (darker/faded)
        for ($i = 0; $i < 4; $i++) {
            $this->raylib->DrawRectangle(
                (int) ($startX + $i * 22),
                (int) $startY,
                20,
                20,
                $this->raylib->GetColor(0x666666FF) // Gray
            );
        }

        // Add some "X" eyes to show it's dead
        $headX = (int) $startX;
        $headY = (int) $startY;

        $this->raylib->DrawText('X', $headX + 3, $headY + 2, 12, $this->titleColor);
        $this->raylib->DrawText('X', $headX + 12, $headY + 2, 12, $this->titleColor);
    }
}
