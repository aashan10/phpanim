<?php

declare(strict_types=1);

use Phpanim\Plugins\SceneManagerPlugin;

class GameState
{
    private ?SceneManagerPlugin $sceneManager = null;

    public const CELL_SIZE = 20;

    public const GRID_WIDTH = 30;

    public const GRID_HEIGHT = 20;

    public array $snake = [[15, 10]]; // Start in center

    public array $food = [10, 5];

    public array $direction = [1, 0]; // Moving right

    public array $nextDirection = [1, 0];

    public int $score = 0;

    public bool $gameOver = false;

    public float $lastMoveTime = 0.0;

    public float $moveInterval = 0.15; // Snake moves every 150ms

    public function __construct(?SceneManagerPlugin $sceneManager = null)
    {
        $this->sceneManager = $sceneManager;
    }

    public function getCurrentScene(): ?string
    {
        return $this->sceneManager?->getCurrentSceneName();
    }

    public function transitionTo(string $sceneName): bool
    {
        return $this->sceneManager?->transitionTo($sceneName) ?? false;
    }

    public function reset(): void
    {
        $this->snake = [[15, 10]];
        $this->generateFood();
        $this->direction = [1, 0];
        $this->nextDirection = [1, 0];
        $this->score = 0;
        $this->gameOver = false;
        $this->lastMoveTime = 0.0;
        $this->moveInterval = 0.15; // Reset speed to initial value
        $this->transitionTo('game');
    }

    public function reload(): void
    {
        $this->snake = [[15, 10]];
        $this->generateFood();
        $this->direction = [1, 0];
        $this->nextDirection = [1, 0];
        $this->score = 0;
        $this->gameOver = false;
        $this->lastMoveTime = 0.0;
        $this->moveInterval = 0.15; // Reset speed to initial value
        $this->transitionTo('welcome');
    }

    public function generateFood(): void
    {
        do {
            $this->food = [
                rand(0, self::GRID_WIDTH - 1),
                rand(0, self::GRID_HEIGHT - 1),
            ];
        } while ($this->isFoodOnSnake());
    }

    private function isFoodOnSnake(): bool
    {
        foreach ($this->snake as $segment) {
            if ($segment[0] === $this->food[0] && $segment[1] === $this->food[1]) {
                return true;
            }
        }

        return false;
    }

    public function moveSnake(float $currentTime): void
    {
        if ($currentTime - $this->lastMoveTime < $this->moveInterval) {
            return;
        }

        $this->lastMoveTime = $currentTime;
        $this->direction = $this->nextDirection;

        $head = $this->snake[0];
        $newHead = [
            $head[0] + $this->direction[0],
            $head[1] + $this->direction[1],
        ];

        // Check wall collision
        if ($newHead[0] < 0 || $newHead[0] >= self::GRID_WIDTH ||
            $newHead[1] < 0 || $newHead[1] >= self::GRID_HEIGHT) {
            $this->gameOver = true;
            $this->transitionTo('gameOver');

            return;
        }

        // Check self collision
        foreach ($this->snake as $segment) {
            if ($segment[0] === $newHead[0] && $segment[1] === $newHead[1]) {
                $this->gameOver = true;
                $this->transitionTo('gameOver');

                return;
            }
        }

        array_unshift($this->snake, $newHead);

        // Check food collision
        if ($newHead[0] === $this->food[0] && $newHead[1] === $this->food[1]) {
            $this->score += 10;
            $this->generateFood();

            // Increase speed slightly
            $this->moveInterval = max(0.05, $this->moveInterval - 0.005);
        } else {
            array_pop($this->snake);
        }
    }

    public function changeDirection(int $x, int $y): void
    {
        // Prevent reversing into itself
        if ($x === -$this->direction[0] && $y === -$this->direction[1]) {
            return;
        }
        $this->nextDirection = [$x, $y];
    }
}
