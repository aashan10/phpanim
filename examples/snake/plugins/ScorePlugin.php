<?php

use Phpanim\Plugins\PluginInterface;
use Phpanim\Raylib\Raylib;

class ScorePlugin implements PluginInterface
{
    private mixed $textColor;

    public function __construct(
        private Raylib $raylib,
        private GameState $gameState
    ) {}

    public function register(): void
    {
        $this->textColor = $this->raylib->GetColor(0xFFFFFFFF); // White
    }

    public function render(): void
    {
        // Only render score during game and game over scenes
        if ($this->gameState->getCurrentScene() === 'welcome') {
            return;
        }

        $scoreText = 'Score: '.$this->gameState->score;
        $this->raylib->DrawText($scoreText, 10, 10, 24, $this->textColor);

        // Show high score or additional info during game over
        if ($this->gameState->getCurrentScene() === 'gameOver') {
            $this->raylib->DrawText('Final Score: '.$this->gameState->score, 10, 40, 20, $this->textColor);
        }
    }
}
