<?php

declare(strict_types=1);

namespace Phpanim\Events;

readonly class GamepadAxisMoved
{
    public function __construct(
        public int $gamepad,
        public int $axis,
        public float $value,
        public float $timestamp
    ) {}
}