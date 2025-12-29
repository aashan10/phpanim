<?php

declare(strict_types=1);

namespace Phpanim\Events;

readonly class MouseClicked
{
    public function __construct(
        public float $x,
        public float $y,
        public int $button,
        public float $timestamp
    ) {}
}
