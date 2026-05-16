<?php

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\KeyPressed;
use Phpanim\Input\Keyboard;
use Phpanim\Raylib\Raylib;
use Phpanim\Scenes\SceneInterface;

class GameScene implements SceneInterface
{
    private mixed $snakeColor;

    private mixed $foodColor;

    private mixed $gridColor;

    private bool $isDone = false;

    private int $offsetX;

    private int $offsetY;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private GameState $gameState
    ) {}

    public function register(): void
    {
        $this->snakeColor = $this->raylib->GetColor(0x00FF00FF); // Green
        $this->foodColor = $this->raylib->GetColor(0xFF0000FF);  // Red
        $this->gridColor = $this->raylib->GetColor(0x333333FF);  // Dark gray

        // Calculate offset to center the game grid
        $screenWidth = 1600;
        $screenHeight = 900;
        $gameWidth = GameState::GRID_WIDTH * GameState::CELL_SIZE;
        $gameHeight = GameState::GRID_HEIGHT * GameState::CELL_SIZE;

        $this->offsetX = ($screenWidth - $gameWidth) / 2;
        $this->offsetY = ($screenHeight - $gameHeight) / 2;

        $this->dispatcher->on(Events::KeyPressed, function (KeyPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'game') {
                return;
            }

            match ($event->keyName) {
                Keyboard::UP, Keyboard::W => $this->gameState->changeDirection(0, -1),
                Keyboard::DOWN, Keyboard::S => $this->gameState->changeDirection(0, 1),
                Keyboard::LEFT, Keyboard::A => $this->gameState->changeDirection(-1, 0),
                Keyboard::RIGHT, Keyboard::D => $this->gameState->changeDirection(1, 0),
                Keyboard::ESCAPE => $this->gameState->transitionTo('gameOver'),
                Keyboard::R => $this->gameState->reload(),
                default => null
            };
        });
    }

    public function isDone(): bool
    {
        return $this->isDone || $this->gameState->gameOver;
    }

    public function reset(): void
    {
        $this->isDone = false;
    }

    public function render(): void
    {
        $currentTime = microtime(true);

        // Update game logic
        if (! $this->gameState->gameOver) {
            $this->gameState->moveSnake($currentTime);
        }

        if ($this->gameState->gameOver) {
            $this->isDone = true;

            return;
        }

        // Draw grid background
        $this->drawGrid();

        // Draw food
        $this->raylib->DrawRectangle(
            (int) ($this->offsetX + $this->gameState->food[0] * GameState::CELL_SIZE),
            (int) ($this->offsetY + $this->gameState->food[1] * GameState::CELL_SIZE),
            GameState::CELL_SIZE,
            GameState::CELL_SIZE,
            $this->foodColor
        );

        // Draw snake
        foreach ($this->gameState->snake as $index => $segment) {
            $color = $index === 0 ? $this->snakeColor : $this->raylib->GetColor(0x008800FF); // Darker green for body

            $this->raylib->DrawRectangle(
                (int) ($this->offsetX + $segment[0] * GameState::CELL_SIZE),
                (int) ($this->offsetY + $segment[1] * GameState::CELL_SIZE),
                GameState::CELL_SIZE - 1, // Small gap between segments
                GameState::CELL_SIZE - 1,
                $color
            );
        }

        // Draw game instructions
        $this->raylib->DrawText(
            'Use arrow keys or WASD to move',
            10,
            800,
            16,
            $this->raylib->GetColor(0xFFFFFFFF)
        );
        $this->raylib->DrawText(
            'Use D-Pad or Left Stick to move',
            10,
            820,
            16,
            $this->raylib->GetColor(0xFFFFFFFF)
        );

        $this->raylib->DrawText(
            'ESC to quit',
            10,
            840,
            16,
            $this->raylib->GetColor(0xFFFFFFFF)
        );

        $this->raylib->DrawText(
            'R to reload',
            10,
            860,
            16,
            $this->raylib->GetColor(0xFFFFFFFF)
        );

        $this->raylib->DrawText(
            'START to quit',
            10,
            880,
            16,
            $this->raylib->GetColor(0xFFFFFFFF)
        );
    }

    private function drawGrid(): void
    {
        // Draw vertical lines
        for ($x = 0; $x <= GameState::GRID_WIDTH; $x++) {
            $this->raylib->DrawLine(
                (int) ($this->offsetX + $x * GameState::CELL_SIZE),
                (int) $this->offsetY,
                (int) ($this->offsetX + $x * GameState::CELL_SIZE),
                (int) ($this->offsetY + GameState::GRID_HEIGHT * GameState::CELL_SIZE),
                $this->gridColor
            );
        }

        // Draw horizontal lines
        for ($y = 0; $y <= GameState::GRID_HEIGHT; $y++) {
            $this->raylib->DrawLine(
                (int) $this->offsetX,
                (int) ($this->offsetY + $y * GameState::CELL_SIZE),
                (int) ($this->offsetX + GameState::GRID_WIDTH * GameState::CELL_SIZE),
                (int) ($this->offsetY + $y * GameState::CELL_SIZE),
                $this->gridColor
            );
        }
    }
}
