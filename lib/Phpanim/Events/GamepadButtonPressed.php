<?php

declare(strict_types=1);

namespace Phpanim\Events;

readonly class GamepadButtonPressed
{
    public function __construct(
        public int $gamepad,
        public int $button,
        public string $buttonName,
        public float $timestamp
    ) {}
}