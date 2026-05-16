<?php

declare(strict_types=1);

use Phpanim\Plugins\SceneManagerPlugin;

class DinoGameState
{
    private ?SceneManagerPlugin $sceneManager = null;

    // Game constants
    public const GRAVITY = 1200.0;

    public const JUMP_VELOCITY = -600.0;  // Increased jump velocity for higher jumps

    public const GROUND_Y = 700;

    public const DINO_WIDTH = 40;

    public const DINO_HEIGHT = 40;

    public const CACTUS_WIDTH = 20;

    public const CACTUS_HEIGHT = 40;

    public const GAME_SPEED_INITIAL = 200.0;

    public const GAME_SPEED_INCREMENT = 5.0;

    public const CACTUS_SPAWN_MIN_INTERVAL = 1.2;  // Minimum time between cacti

    public const CACTUS_SPAWN_MAX_INTERVAL = 3.0;  // Maximum time between cacti

    // Dino state
    public float $dinoX = 100.0;

    public float $dinoY = self::GROUND_Y;

    public float $dinoVelocityY = 0.0;

    public bool $isJumping = false;

    public bool $isDucking = false;

    // Game state
    public array $cacti = [];

    public float $gameSpeed = self::GAME_SPEED_INITIAL;

    public int $score = 0;

    public int $highScore = 0;

    public bool $gameOver = false;

    public float $lastCactusSpawn = 0.0;

    public float $nextCactusSpawnTime = 0.0;  // When next cactus should spawn

    public float $gameTime = 0.0;

    public float $lastScoreTime = 0.0;

    // Background elements
    public array $clouds = [];

    public float $groundOffset = 0.0;

    // Sound effects
    private ?SoundManager $soundManager = null;

    public bool $soundEnabled = true;

    // Visual feedback for sound effects
    public float $jumpSoundTimer = 0.0;

    public float $hitSoundTimer = 0.0;

    public float $scoreSoundTimer = 0.0;

    public function __construct(?SceneManagerPlugin $sceneManager = null, ?SoundManager $soundManager = null)
    {
        $this->sceneManager = $sceneManager;
        $this->soundManager = $soundManager;
        $this->initializeClouds();
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
        $this->dinoX = 100.0;
        $this->dinoY = self::GROUND_Y;
        $this->dinoVelocityY = 0.0;
        $this->isJumping = false;
        $this->isDucking = false;

        $this->cacti = [];
        $this->gameSpeed = self::GAME_SPEED_INITIAL;
        $this->score = 0;
        $this->gameOver = false;
        $this->lastCactusSpawn = 0.0;
        $this->nextCactusSpawnTime = $this->generateNextCactusTime();
        $this->gameTime = 0.0;
        $this->lastScoreTime = 0.0;
        $this->groundOffset = 0.0;

        $this->initializeClouds();
        $this->transitionTo('game');
    }

    public function reload(): void
    {
        $this->reset();
        $this->transitionTo('welcome');
    }

    public function jump(): void
    {
        if (! $this->isJumping && ! $this->gameOver) {
            $this->dinoVelocityY = self::JUMP_VELOCITY;
            $this->isJumping = true;
            $this->isDucking = false;

            // Play jump sound effect
            if ($this->soundEnabled && $this->soundManager) {
                $this->soundManager->playJump();
                $this->jumpSoundTimer = 0.2; // Show visual feedback for 0.2 seconds
            }
        }
    }

    public function duck(): void
    {
        if (! $this->isJumping && ! $this->gameOver) {
            $this->isDucking = true;
        }
    }

    public function stopDucking(): void
    {
        $this->isDucking = false;
    }

    public function update(float $deltaTime): void
    {
        if ($this->gameOver) {
            return;
        }

        $this->gameTime += $deltaTime;

        // Update sound effect timers
        $this->updateSoundTimers($deltaTime);

        // Update dino physics
        $this->updateDino($deltaTime);

        // Update background
        $this->updateBackground($deltaTime);

        // Spawn cacti
        $this->spawnCacti();

        // Update cacti
        $this->updateCacti($deltaTime);

        // Update score
        $this->updateScore($deltaTime);

        // Increase game speed over time
        $this->gameSpeed += self::GAME_SPEED_INCREMENT * $deltaTime;

        // Check collisions
        $this->checkCollisions();
    }

    private function updateDino(float $deltaTime): void
    {
        if ($this->isJumping) {
            $this->dinoVelocityY += self::GRAVITY * $deltaTime;
            $this->dinoY += $this->dinoVelocityY * $deltaTime;

            if ($this->dinoY >= self::GROUND_Y) {
                $this->dinoY = self::GROUND_Y;
                $this->dinoVelocityY = 0.0;
                $this->isJumping = false;
            }
        }
    }

    private function updateBackground(float $deltaTime): void
    {
        // Update ground offset for scrolling effect
        $this->groundOffset += $this->gameSpeed * $deltaTime;
        if ($this->groundOffset >= 20) {
            $this->groundOffset = 0;
        }

        // Update clouds
        foreach ($this->clouds as &$cloud) {
            $cloud['x'] -= ($this->gameSpeed * 0.3) * $deltaTime;
            if ($cloud['x'] + 60 < 0) {
                $cloud['x'] = 1600 + rand(100, 300);
                $cloud['y'] = rand(100, 300);
            }
        }
    }

    private function spawnCacti(): void
    {
        if ($this->gameTime >= $this->nextCactusSpawnTime) {
            $this->cacti[] = [
                'x' => 1600.0,
                'y' => self::GROUND_Y,
                'width' => self::CACTUS_WIDTH,
                'height' => self::CACTUS_HEIGHT,
                'type' => rand(1, 3), // Different cactus types
            ];

            $this->lastCactusSpawn = $this->gameTime;
            $this->nextCactusSpawnTime = $this->gameTime + $this->generateNextCactusTime();
        }
    }

    private function generateNextCactusTime(): float
    {
        // Random interval between min and max, with slight bias toward shorter intervals
        $minInterval = self::CACTUS_SPAWN_MIN_INTERVAL;
        $maxInterval = self::CACTUS_SPAWN_MAX_INTERVAL;

        // As game speed increases, reduce spawn intervals slightly for more challenge
        $speedFactor = min(1.0, $this->gameSpeed / 400.0); // Cap at 400 speed
        $adjustedMax = $maxInterval - ($speedFactor * 0.5);
        $adjustedMax = max($minInterval + 0.2, $adjustedMax); // Ensure min gap

        return $minInterval + (float) rand() / (float) getrandmax() * ($adjustedMax - $minInterval);
    }

    private function updateCacti(float $deltaTime): void
    {
        foreach ($this->cacti as $index => &$cactus) {
            $cactus['x'] -= $this->gameSpeed * $deltaTime;

            // Remove cacti that are off-screen
            if ($cactus['x'] + $cactus['width'] < 0) {
                unset($this->cacti[$index]);
            }
        }

        $this->cacti = array_values($this->cacti); // Reindex array
    }

    private function updateScore(float $deltaTime): void
    {
        if ($this->gameTime - $this->lastScoreTime >= 0.1) {
            $oldScore = $this->score;
            $this->score += 1;
            $this->lastScoreTime = $this->gameTime;

            // Play score sound every 100 points
            if ($this->soundEnabled && $this->soundManager &&
                $oldScore > 0 && $this->score % 100 === 0) {
                $this->soundManager->playScore();
                $this->scoreSoundTimer = 0.3; // Show visual feedback for 0.3 seconds
            }
        }
    }

    private function checkCollisions(): void
    {
        $dinoRect = [
            'x' => $this->dinoX,
            'y' => $this->dinoY - ($this->isDucking ? 20 : 0),
            'width' => self::DINO_WIDTH,
            'height' => $this->isDucking ? 20 : self::DINO_HEIGHT,
        ];

        foreach ($this->cacti as $cactus) {
            if ($this->isColliding($dinoRect, $cactus)) {
                $this->gameOver = true;
                if ($this->score > $this->highScore) {
                    $this->highScore = $this->score;
                }

                // Play collision sound effect
                if ($this->soundEnabled && $this->soundManager) {
                    $this->soundManager->playHit();
                    $this->hitSoundTimer = 0.5; // Show visual feedback for 0.5 seconds
                }

                $this->transitionTo('gameOver');
                break;
            }
        }
    }

    private function isColliding(array $rect1, array $cactus): bool
    {
        return $rect1['x'] < $cactus['x'] + $cactus['width'] &&
               $cactus['x'] < $rect1['x'] + $rect1['width'] &&
               $rect1['y'] < $cactus['y'] + $cactus['height'] &&
               $cactus['y'] < $rect1['y'] + $rect1['height'];
    }

    private function initializeClouds(): void
    {
        $this->clouds = [];
        for ($i = 0; $i < 5; $i++) {
            $this->clouds[] = [
                'x' => rand(0, 1600),
                'y' => rand(100, 300),
            ];
        }
    }

    public function getDinoRect(): array
    {
        return [
            'x' => $this->dinoX,
            'y' => $this->dinoY - ($this->isDucking ? 20 : 0),
            'width' => self::DINO_WIDTH,
            'height' => $this->isDucking ? 20 : self::DINO_HEIGHT,
        ];
    }

    public function setSoundManager(SoundManager $soundManager): void
    {
        $this->soundManager = $soundManager;
    }

    public function toggleSound(): void
    {
        $this->soundEnabled = ! $this->soundEnabled;
    }

    private function updateSoundTimers(float $deltaTime): void
    {
        if ($this->jumpSoundTimer > 0) {
            $this->jumpSoundTimer -= $deltaTime;
        }
        if ($this->hitSoundTimer > 0) {
            $this->hitSoundTimer -= $deltaTime;
        }
        if ($this->scoreSoundTimer > 0) {
            $this->scoreSoundTimer -= $deltaTime;
        }
    }
}
