<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Types;

final class Vec2
{
    public function __construct(
        public float $x = 0.0,
        public float $y = 0.0,
    ) {}
}
