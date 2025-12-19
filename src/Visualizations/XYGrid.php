<?php
declare(strict_types=1);

namespace Aashan\Phpanim\Visualizations;

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Types\Vec2;

class XYGrid
{
    public function __construct(
        public Vec2 $origin,
        public float $spacing = 20.0,
        public float $unitSize = 20.0, // Pixels per unit for labels
        public int $labelInterval = 5, // Show label every N grid lines
        public int $gridColor = 0xEEEEEEFF,
        public int $majorGridColor = 0xCCCCCCFF,
        public int $axisColor = 0x000000FF,
        public int $labelColor = 0x333333FF,
        public int $fontSize = 10,
        public bool $showLabels = true,
        public bool $showTicks = true,
        public float $tickSize = 5.0,
        public int $labelPrecision = 0,
    ) {}

    public function render(Raylib $rl): void
    {
        $screenWidth = $rl->GetScreenWidth();
        $screenHeight = $rl->GetScreenHeight();
        $gridColor = $rl->GetColor($this->gridColor);
        $majorGridColor = $rl->GetColor($this->majorGridColor);
        $axisColor = $rl->GetColor($this->axisColor);
        $labelColor = $rl->GetColor($this->labelColor);

        $gridIndex = 0;

        // Draw vertical grid lines (X-axis grid)
        // Right side from origin
        $gridIndex = 0;
        for ($x = $this->origin->x; $x < $screenWidth; $x += $this->spacing) {
            $isMajor = ($gridIndex % $this->labelInterval) === 0;
            $lineColor = $isMajor ? $majorGridColor : $gridColor;

            if ($x != $this->origin->x) {
                $rl->DrawLine((int) $x, 0, (int) $x, $screenHeight, $lineColor);

                // Draw tick and label only at major intervals
                if ($isMajor) {
                    if ($this->showTicks) {
                        $rl->DrawLine(
                            (int) $x,
                            (int) ($this->origin->y - $this->tickSize),
                            (int) $x,
                            (int) ($this->origin->y + $this->tickSize),
                            $axisColor,
                        );
                    }

                    if ($this->showLabels) {
                        $graphValue = ($x - $this->origin->x) / $this->unitSize;
                        $label = $this->formatLabel($graphValue);
                        $labelWidth = $rl->MeasureText($label, $this->fontSize);
                        $rl->DrawText(
                            $label,
                            (int) ($x - ($labelWidth / 2)),
                            (int) ($this->origin->y + $this->tickSize + 3),
                            $this->fontSize,
                            $labelColor,
                        );
                    }
                }
            }
            $gridIndex++;
        }

        // Left side from origin
        $gridIndex = 0;
        for ($x = $this->origin->x - $this->spacing; $x > 0; $x -= $this->spacing) {
            $gridIndex++;
            $isMajor = ($gridIndex % $this->labelInterval) === 0;
            $lineColor = $isMajor ? $majorGridColor : $gridColor;

            $rl->DrawLine((int) $x, 0, (int) $x, $screenHeight, $lineColor);

            if ($isMajor) {
                if ($this->showTicks) {
                    $rl->DrawLine(
                        (int) $x,
                        (int) ($this->origin->y - $this->tickSize),
                        (int) $x,
                        (int) ($this->origin->y + $this->tickSize),
                        $axisColor,
                    );
                }

                if ($this->showLabels) {
                    $graphValue = ($x - $this->origin->x) / $this->unitSize;
                    $label = $this->formatLabel($graphValue);
                    $labelWidth = $rl->MeasureText($label, $this->fontSize);
                    $rl->DrawText(
                        $label,
                        (int) ($x - ($labelWidth / 2)),
                        (int) ($this->origin->y + $this->tickSize + 3),
                        $this->fontSize,
                        $labelColor,
                    );
                }
            }
        }

        // Draw horizontal grid lines (Y-axis grid)
        // Below origin
        $gridIndex = 0;
        for ($y = $this->origin->y; $y < $screenHeight; $y += $this->spacing) {
            $isMajor = ($gridIndex % $this->labelInterval) === 0;
            $lineColor = $isMajor ? $majorGridColor : $gridColor;

            if ($y != $this->origin->y) {
                $rl->DrawLine(0, (int) $y, $screenWidth, (int) $y, $lineColor);

                if ($isMajor) {
                    if ($this->showTicks) {
                        $rl->DrawLine(
                            (int) ($this->origin->x - $this->tickSize),
                            (int) $y,
                            (int) ($this->origin->x + $this->tickSize),
                            (int) $y,
                            $axisColor,
                        );
                    }

                    if ($this->showLabels) {
                        $graphValue = -($y - $this->origin->y) / $this->unitSize;
                        $label = $this->formatLabel($graphValue);
                        $labelWidth = $rl->MeasureText($label, $this->fontSize);
                        $rl->DrawText(
                            $label,
                            (int) ($this->origin->x - $labelWidth - $this->tickSize - 3),
                            (int) ($y - ($this->fontSize / 2)),
                            $this->fontSize,
                            $labelColor,
                        );
                    }
                }
            }
            $gridIndex++;
        }

        // Above origin
        $gridIndex = 0;
        for ($y = $this->origin->y - $this->spacing; $y > 0; $y -= $this->spacing) {
            $gridIndex++;
            $isMajor = ($gridIndex % $this->labelInterval) === 0;
            $lineColor = $isMajor ? $majorGridColor : $gridColor;

            $rl->DrawLine(0, (int) $y, $screenWidth, (int) $y, $lineColor);

            if ($isMajor) {
                if ($this->showTicks) {
                    $rl->DrawLine(
                        (int) ($this->origin->x - $this->tickSize),
                        (int) $y,
                        (int) ($this->origin->x + $this->tickSize),
                        (int) $y,
                        $axisColor,
                    );
                }

                if ($this->showLabels) {
                    $graphValue = -($y - $this->origin->y) / $this->unitSize;
                    $label = $this->formatLabel($graphValue);
                    $labelWidth = $rl->MeasureText($label, $this->fontSize);
                    $rl->DrawText(
                        $label,
                        (int) ($this->origin->x - $labelWidth - $this->tickSize - 3),
                        (int) ($y - ($this->fontSize / 2)),
                        $this->fontSize,
                        $labelColor,
                    );
                }
            }
        }

        // Draw origin label
        if ($this->showLabels) {
            $rl->DrawText(
                '0',
                (int) ($this->origin->x + $this->tickSize + 3),
                (int) ($this->origin->y + $this->tickSize + 3),
                $this->fontSize,
                $labelColor,
            );
        }

        // Draw axes (on top of grid)
        $xAxisStart = $rl->struct('Vector2', ['x' => 0, 'y' => $this->origin->y]);
        $xAxisEnd = $rl->struct('Vector2', ['x' => $screenWidth, 'y' => $this->origin->y]);
        $yAxisStart = $rl->struct('Vector2', ['x' => $this->origin->x, 'y' => 0]);
        $yAxisEnd = $rl->struct('Vector2', ['x' => $this->origin->x, 'y' => $screenHeight]);

        $rl->DrawLineEx($xAxisStart, $xAxisEnd, 2.0, $axisColor);
        $rl->DrawLineEx($yAxisStart, $yAxisEnd, 2.0, $axisColor);
    }

    private function formatLabel(float $value): string
    {
        if (abs($value) < 0.0001) {
            return '0';
        }

        $formatted = number_format($value, $this->labelPrecision, '.', '');

        if (str_contains($formatted, '.')) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
        }

        return $formatted;
    }
}
