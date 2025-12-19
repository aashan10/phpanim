<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Traits;

use Aashan\Phpanim\Types\Vec2;

trait AnimatesVec2
{
    abstract public function tween(float $duration, callable $updateCallback): static;

    public function rotateVec2(float $duration, Vec2 $vec, float $degrees): static
    {
        $initialX = $vec->x;
        $initialY = $vec->y;

        return $this->tween($duration, function (float $t) use ($vec, $initialX, $initialY, $degrees) {
            $radians = deg2rad($degrees * $t);
            $cos = cos($radians);
            $sin = sin($radians);

            $vec->x = ($initialX * $cos) - ($initialY * $sin);
            $vec->y = ($initialX * $sin) + ($initialY * $cos);
        });
    }
}
