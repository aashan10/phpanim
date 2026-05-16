<?php

use Phpanim\Plugins\PluginInterface;
use Phpanim\Raylib\Raylib;

class ScorePlugin implements PluginInterface
{
    private mixed $scoreColor;

    private mixed $highScoreColor;

    public function __construct(
        private Raylib $raylib,
        private DinoGameState $gameState
    ) {}

    public function register(): void
    {
        $this->scoreColor = $this->raylib->GetColor(0x535353FF);     // Dark gray
        $this->highScoreColor = $this->raylib->GetColor(0x535353FF); // Dark gray
    }

    public function render(): void
    {
        $currentScene = $this->gameState->getCurrentScene();

        // Only render score during game scene
        if ($currentScene !== 'game') {
            return;
        }

        // Display current score (top right)
        $scoreText = str_pad((string) $this->gameState->score, 5, '0', STR_PAD_LEFT);
        $this->raylib->DrawText($scoreText, 1600 - 120, 50, 20, $this->scoreColor);

        // Display high score (top right, above current score)
        if ($this->gameState->highScore > 0) {
            $highScoreText = 'HI '.str_pad((string) $this->gameState->highScore, 5, '0', STR_PAD_LEFT);
            $this->raylib->DrawText($highScoreText, 1600 - 150, 20, 20, $this->highScoreColor);
        }

        // Display game speed indicator (optional debug info)
        if ($this->shouldShowDebugInfo()) {
            $speedText = 'Speed: '.(int) $this->gameState->gameSpeed;
            $this->raylib->DrawText($speedText, 10, 50, 16, $this->raylib->GetColor(0x888888FF));

            $timeText = 'Time: '.number_format($this->gameState->gameTime, 1).'s';
            $this->raylib->DrawText($timeText, 10, 70, 16, $this->raylib->GetColor(0x888888FF));
        }
    }

    private function shouldShowDebugInfo(): bool
    {
        // You could add a debug flag to the game state, or check for a specific key press
        // For now, let's keep it simple and not show debug info
        return false;
    }
}
