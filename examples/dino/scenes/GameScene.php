<?php

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\GamepadAxisMoved;
use Phpanim\Events\GamepadButtonPressed;
use Phpanim\Events\KeyPressed;
use Phpanim\Events\KeyReleased;
use Phpanim\Input\Gamepad;
use Phpanim\Input\Keyboard;
use Phpanim\Raylib\Raylib;
use Phpanim\Scenes\SceneInterface;

class GameScene implements SceneInterface
{
    private mixed $dinoColor;

    private mixed $cactusColor;

    private mixed $cloudColor;

    private mixed $groundColor;

    private mixed $backgroundColor;

    private bool $isDone = false;

    private float $dinoAnimationTimer = 0.0;

    private bool $dinoRunFrame = false;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private DinoGameState $gameState
    ) {}

    public function register(): void
    {
        $this->dinoColor = $this->raylib->GetColor(0x535353FF);      // Dark gray
        $this->cactusColor = $this->raylib->GetColor(0x00AA00FF);    // Green
        $this->cloudColor = $this->raylib->GetColor(0xF0F0F0FF);     // Light gray
        $this->groundColor = $this->raylib->GetColor(0x535353FF);    // Dark gray
        $this->backgroundColor = $this->raylib->GetColor(0xF7F7F7FF); // Light gray background

        $this->dispatcher->on(Events::KeyPressed, function (KeyPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'game') {
                return;
            }

            match ($event->keyName) {
                Keyboard::SPACE, Keyboard::UP, Keyboard::W => $this->gameState->jump(),
                Keyboard::DOWN, Keyboard::S => $this->gameState->duck(),
                Keyboard::ESCAPE => $this->gameState->transitionTo('gameOver'),
                Keyboard::R => $this->gameState->reload(),
                Keyboard::M => $this->gameState->toggleSound(), // Toggle sound on/off
                default => null
            };
        });

        $this->dispatcher->on(Events::KeyReleased, function (KeyReleased $event) {
            if ($this->gameState->getCurrentScene() !== 'game') {
                return;
            }

            if ($event->keyName === Keyboard::DOWN || $event->keyName === Keyboard::S) {
                $this->gameState->stopDucking();
            }
        });

        $this->dispatcher->on(Events::GamepadButtonPressed, function (GamepadButtonPressed $event) {
            if ($this->gameState->getCurrentScene() !== 'game') {
                return;
            }

            match ($event->button) {
                Gamepad::BUTTON_RIGHT_FACE_DOWN_ID, // A button
                Gamepad::BUTTON_LEFT_FACE_UP_ID => $this->gameState->jump(), // D-pad up
                Gamepad::BUTTON_LEFT_FACE_DOWN_ID => $this->gameState->duck(), // D-pad down
                Gamepad::BUTTON_MIDDLE_LEFT_ID => $this->gameState->transitionTo('gameOver'), // Select/Back
                default => null
            };
        });

        $this->dispatcher->on(Events::GamepadAxisMoved, function (GamepadAxisMoved $event) {
            if ($this->gameState->getCurrentScene() !== 'game') {
                return;
            }

            // Left stick vertical axis
            if ($event->axis === Gamepad::AXIS_LEFT_Y_ID) {
                if ($event->value < -0.5) { // Up
                    $this->gameState->jump();
                } elseif ($event->value > 0.5) { // Down
                    $this->gameState->duck();
                } else {
                    $this->gameState->stopDucking();
                }
            }
        });
    }

    public function isDone(): bool
    {
        return $this->isDone || $this->gameState->gameOver;
    }

    public function reset(): void
    {
        $this->isDone = false;
        $this->dinoAnimationTimer = 0.0;
        $this->dinoRunFrame = false;
    }

    public function render(): void
    {
        $deltaTime = $this->raylib->GetFrameTime();

        // Update game logic
        if (! $this->gameState->gameOver) {
            $this->gameState->update($deltaTime);
            $this->updateAnimations($deltaTime);
        }

        if ($this->gameState->gameOver) {
            $this->isDone = true;

            return;
        }

        // Clear background
        $this->raylib->ClearBackground($this->backgroundColor);

        // Draw background elements
        $this->drawBackground();

        // Draw ground
        $this->drawGround();

        // Draw clouds
        $this->drawClouds();

        // Draw cacti
        $this->drawCacti();

        // Draw dino
        $this->drawDino();

        // Draw game info
        $this->drawGameInfo();

        // Draw sound effect visual feedback
        $this->drawSoundFeedback();
    }

    private function updateAnimations(float $deltaTime): void
    {
        // Update dino running animation
        $this->dinoAnimationTimer += $deltaTime;
        if ($this->dinoAnimationTimer >= 0.1) { // Change frame every 100ms
            $this->dinoRunFrame = ! $this->dinoRunFrame;
            $this->dinoAnimationTimer = 0.0;
        }
    }

    private function drawBackground(): void
    {
        // Simple gradient or solid background is already cleared above
    }

    private function drawGround(): void
    {
        $groundY = DinoGameState::GROUND_Y + 40;

        // Draw main ground line
        $this->raylib->DrawLine(0, (int) $groundY, 1600, (int) $groundY, $this->groundColor);

        // Draw ground pattern (moving dashes)
        $dashWidth = 20;
        $dashSpacing = 10;
        $totalDashWidth = $dashWidth + $dashSpacing;
        $startOffset = (int) $this->gameState->groundOffset;

        for ($x = -$totalDashWidth + $startOffset; $x < 1600; $x += $totalDashWidth) {
            $this->raylib->DrawRectangle(
                $x,
                (int) $groundY + 1,
                $dashWidth,
                2,
                $this->groundColor
            );
        }
    }

    private function drawClouds(): void
    {
        foreach ($this->gameState->clouds as $cloud) {
            $this->drawCloud($cloud['x'], $cloud['y']);
        }
    }

    private function drawCloud(float $x, float $y): void
    {
        // Simple cloud made of rectangles
        $this->raylib->DrawRectangle((int) $x, (int) $y + 10, 40, 20, $this->cloudColor);
        $this->raylib->DrawRectangle((int) $x + 10, (int) $y, 20, 20, $this->cloudColor);
        $this->raylib->DrawRectangle((int) $x + 20, (int) $y + 15, 30, 15, $this->cloudColor);
    }

    private function drawCacti(): void
    {
        foreach ($this->gameState->cacti as $cactus) {
            $this->drawCactus($cactus);
        }
    }

    private function drawCactus(array $cactus): void
    {
        $x = (int) $cactus['x'];
        $y = (int) $cactus['y'];
        $width = $cactus['width'];
        $height = $cactus['height'];
        $type = $cactus['type'];

        // Main cactus body
        $this->raylib->DrawRectangle($x, $y, $width, $height, $this->cactusColor);

        // Different cactus types with different shapes
        switch ($type) {
            case 1:
                // Simple cactus
                break;
            case 2:
                // Cactus with side arms
                $this->raylib->DrawRectangle($x - 5, $y + 10, 8, 15, $this->cactusColor);
                $this->raylib->DrawRectangle($x + $width - 3, $y + 15, 8, 10, $this->cactusColor);
                break;
            case 3:
                // Tall thin cactus
                $this->raylib->DrawRectangle($x + 5, $y - 10, 10, 10, $this->cactusColor);
                break;
        }
    }

    private function drawDino(): void
    {
        $dinoRect = $this->gameState->getDinoRect();
        $x = (int) $dinoRect['x'];
        $y = (int) $dinoRect['y'];
        $width = $dinoRect['width'];
        $height = $dinoRect['height'];

        // Draw dino body
        $this->raylib->DrawRectangle($x, $y, $width, $height, $this->dinoColor);

        if (! $this->gameState->isDucking) {
            // Draw dino eye
            $this->raylib->DrawRectangle($x + 25, $y + 8, 4, 4, $this->raylib->GetColor(0x000000FF));

            // Draw running legs animation
            if ($this->dinoRunFrame) {
                $this->raylib->DrawRectangle($x + 5, $y + $height, 8, 5, $this->dinoColor);
                $this->raylib->DrawRectangle($x + 25, $y + $height - 2, 8, 7, $this->dinoColor);
            } else {
                $this->raylib->DrawRectangle($x + 5, $y + $height - 2, 8, 7, $this->dinoColor);
                $this->raylib->DrawRectangle($x + 25, $y + $height, 8, 5, $this->dinoColor);
            }
        } else {
            // Ducking dino - draw eye lower
            $this->raylib->DrawRectangle($x + 25, $y + 5, 4, 4, $this->raylib->GetColor(0x000000FF));
        }

        // Draw tail
        if (! $this->gameState->isDucking) {
            $this->raylib->DrawRectangle($x - 5, $y + 15, 8, 5, $this->dinoColor);
        }
    }

    private function drawGameInfo(): void
    {
        // Draw instructions at bottom
        $soundStatus = $this->gameState->soundEnabled ? 'ON' : 'OFF';
        $this->raylib->DrawText(
            "SPACE/UP: Jump  |  DOWN: Duck  |  ESC: Quit  |  R: Restart  |  M: Sound ($soundStatus)",
            10,
            850,
            16,
            $this->raylib->GetColor(0x535353FF)
        );

        $this->raylib->DrawText(
            'Gamepad: A/UP: Jump  |  DOWN/L-Stick: Duck  |  SELECT: Quit',
            10,
            870,
            16,
            $this->raylib->GetColor(0x535353FF)
        );
    }

    private function drawSoundFeedback(): void
    {
        if (! $this->gameState->soundEnabled) {
            return;
        }

        // Jump feedback - blue flash around dino
        if ($this->gameState->jumpSoundTimer > 0) {
            $alpha = (int) (255 * ($this->gameState->jumpSoundTimer / 0.2));
            $jumpColor = $this->raylib->GetColor(0x0088FF00 | $alpha);
            $dinoRect = $this->gameState->getDinoRect();

            $this->raylib->DrawRectangleLines(
                (int) $dinoRect['x'] - 5,
                (int) $dinoRect['y'] - 5,
                $dinoRect['width'] + 10,
                $dinoRect['height'] + 10,
                $jumpColor
            );
        }

        // Hit feedback - red flash on screen edges
        if ($this->gameState->hitSoundTimer > 0) {
            $alpha = (int) (255 * ($this->gameState->hitSoundTimer / 0.5));
            $hitColor = $this->raylib->GetColor(0xFF000000 | $alpha);

            // Draw red borders
            $this->raylib->DrawRectangle(0, 0, 1600, 10, $hitColor);
            $this->raylib->DrawRectangle(0, 890, 1600, 10, $hitColor);
            $this->raylib->DrawRectangle(0, 0, 10, 900, $hitColor);
            $this->raylib->DrawRectangle(1590, 0, 10, 900, $hitColor);
        }

        // Score feedback - golden sparkle at score position
        if ($this->gameState->scoreSoundTimer > 0) {
            $alpha = (int) (255 * ($this->gameState->scoreSoundTimer / 0.3));
            $scoreColor = $this->raylib->GetColor(0xFFD70000 | $alpha);

            // Draw sparkle effect around score
            for ($i = 0; $i < 8; $i++) {
                $angle = $i * 45 * (3.14159 / 180); // Convert to radians
                $x = 1600 - 60 + (int) (cos($angle) * 20);
                $y = 50 + (int) (sin($angle) * 15);
                $this->raylib->DrawRectangle($x - 1, $y - 1, 3, 3, $scoreColor);
            }
        }
    }
}
