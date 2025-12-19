<?php
declare(strict_types=1);

namespace Aashan\Phpanim\Visualizations;

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Types\Vec2;
use Closure;

class Curve
{
    public function __construct(
        public Vec2 $origin, // Screen coordinate origin
        public Vec2 $min, // Minimum XY bounds (bottom-left corner in graph space)
        public Vec2 $max, // Maximum XY bounds (top-right corner in graph space)
        public Closure $fn,
        public int $color = 0xFF0000FF,
        public float $thickness = 2.0,
        public float $unitSize = 20.0,
        public int $segments = 100,
    ) {}

    public function render(Raylib $rl): void
    {
        // Extract bounds
        $minX = $this->min->x;
        $maxX = $this->max->x;
        $minY = $this->min->y;
        $maxY = $this->max->y;

        $step = ($maxX - $minX) / $this->segments;

        // Collect valid points within bounds
        $validPoints = [];

        for ($i = 0; $i <= $this->segments; $i++) {
            // Calculate graph x coordinate
            $graphX = $minX + ($i * $step);

            // Evaluate function at this x
            $graphY = ($this->fn)($graphX);

            // Clip to Y bounds
            if ($graphY < $minY || $graphY > $maxY) {
                // Point is outside bounds, skip it
                continue;
            }

            // Convert graph coordinates to screen coordinates
            $screenX = $this->origin->x + ($graphX * $this->unitSize);
            $screenY = $this->origin->y - ($graphY * $this->unitSize);

            $validPoints[] = ['x' => $screenX, 'y' => $screenY];
        }

        $pointCount = count($validPoints);

        if ($pointCount < 2) {
            return; // Not enough points to draw a curve
        }

        // Create FFI Vector2 array with only valid points
        $points = $rl->ffi->new("Vector2[{$pointCount}]");

        foreach ($validPoints as $i => $point) {
            $points[$i]->x = $point['x'];
            $points[$i]->y = $point['y'];
        }

        // Draw the curve
        $rl->DrawSplineCatmullRom($points, $pointCount, $this->thickness, $rl->GetColor($this->color));
    }
}
