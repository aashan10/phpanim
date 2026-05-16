<?php

declare(strict_types=1);

use Phpanim\Raylib\Raylib;

class SoundManager
{
    private array $sounds = [];

    private bool $soundEnabled = true;

    private float $volume = 0.5;

    public function __construct(
        private Raylib $raylib
    ) {}

    public function initialize(): void
    {
        // For this demo, we'll use simple console-based sound feedback
        // In a production game, you would load actual sound files or use proper audio generation
        $this->soundEnabled = true;
    }

    public function cleanup(): void
    {
        // Nothing to cleanup for console-based sounds
    }

    public function playSound(string $soundName): void
    {
        if (! $this->soundEnabled) {
            return;
        }

        // For now, use simple console feedback for different sounds
        // In a full implementation, you would use actual sound files or generated audio
        switch ($soundName) {
            case 'jump':
                $this->playSimpleBeep();
                break;
            case 'hit':
                // Different pattern for collision
                $this->playSimpleBeep();
                usleep(50000); // 50ms delay
                $this->playSimpleBeep();
                break;
            case 'score':
                // Higher pitched sound for score
                $this->playSimpleBeep();
                break;
        }
    }

    public function playJump(): void
    {
        $this->playSound('jump');
    }

    public function playHit(): void
    {
        $this->playSound('hit');
    }

    public function playScore(): void
    {
        $this->playSound('score');
    }

    public function setVolume(float $volume): void
    {
        $this->volume = max(0.0, min(1.0, $volume));
    }

    public function enableSound(bool $enabled): void
    {
        $this->soundEnabled = $enabled;
    }

    public function isSoundEnabled(): bool
    {
        return $this->soundEnabled;
    }

    // Simple alternative for basic feedback without actual audio files
    public function playSimpleBeep(): void
    {
        // Visual feedback instead of audio for demo purposes
        // In a real game, you'd want actual sound files or proper raylib audio
        // For now, we'll just track that a sound was "played"
        static $soundCount = 0;
        $soundCount++;

        // You could add visual feedback here if desired
        // For example, flash the screen border or show a sound indicator
    }
}
