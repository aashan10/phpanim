<?php

declare(strict_types=1);

namespace Phpanim\UI;

class Rect
{
    public function __construct(
        public float $x,
        public float $y,
        public float $width,
        public float $height,
    ) {}

    public function contains(float $x, float $y): bool
    {
        return $x >= $this->x
            && $x <= $this->x + $this->width
            && $y >= $this->y
            && $y <= $this->y + $this->height;
    }
}
