<?php

declare(strict_types=1);

namespace Phpanim\UI\Components;

use Phpanim\Events\Events;
use Phpanim\Events\MouseClicked;
use Phpanim\UI\Component;

class FunctionPlot extends Component
{
    private mixed $fn = null;

    private float $t = 0.0;

    private float $speed = 1.5; // how fast $t advances per second

    private int $colorHex = 0x38BDF8FF;

    private string $labelText = '';

    private int $labelOffsetY = 12;

    private ?XYGrid $grid = null;

    private bool $onCurve = false;

    private bool $bezierMode = false;

    private mixed $plotColor = null;

    private mixed $labelColor = null;

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    /**
     * The function to plot. Receives the mathematical x and accumulated time t.
     * Return the mathematical y.
     *
     * Static plot:   ->plot(fn(float $x, float $t): float => $x ** 2)
     * Animated plot: ->plot(fn(float $x, float $t): float => sin($x + $t))
     */
    public function plot(callable $fn): static
    {
        $this->fn = $fn;

        return $this;
    }

    public function grid(XYGrid $grid): static
    {
        $this->grid = $grid;

        return $this;
    }

    public function speed(float $speed): static
    {
        $this->speed = $speed;

        return $this;
    }

    public function reset(): void
    {
        $this->t = 0.0;
    }

    public function color(int $rgba): static
    {
        $this->colorHex = $rgba;

        return $this;
    }

    public function bezier(): static
    {
        $this->bezierMode = true;

        return $this;
    }

    public function label(string $label, int $offsetY = 12): static
    {
        $this->labelText = $label;
        $this->labelOffsetY = $offsetY;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    protected function onMount(): void
    {
        $this->plotColor = $this->raylib->GetColor($this->colorHex);
        $this->labelColor = $this->raylib->GetColor(0x94A3B8FF);

        $this->dispatcher->on(Events::MouseClicked, function (MouseClicked $e): void {
            $this->t = 0.0;
        });
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    protected function onRender(): void
    {
        if ($this->fn === null || $this->grid === null) {
            return;
        }

        $this->t += $this->speed * (float) $this->raylib->GetFrameTime();

        $this->onCurve = $this->isMouseNearCurve();
        $this->drawPlot();

        if ($this->labelText !== '') {
            $this->drawLabel();
        }
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function drawPlot(): void
    {
        $screenXMin = (int) $this->rect->x;
        $screenXMax = (int) ($this->rect->x + $this->rect->width);

        $thickness = $this->onCurve ? 3.0 : 1.5;

        $startVec = $this->raylib->struct('Vector2', ['x' => 0.0, 'y' => 0.0]);
        $endVec = $this->raylib->struct('Vector2', ['x' => 0.0, 'y' => 0.0]);

        $prevX = (float) $screenXMin;
        $prevY = $this->evalAt($prevX);

        for ($screenX = $screenXMin + 1; $screenX <= $screenXMax; $screenX++) {
            $curX = (float) $screenX;
            $curY = $this->evalAt($curX);

            $startVec->x = $prevX;
            $startVec->y = $prevY;
            $endVec->x = $curX;
            $endVec->y = $curY;

            if ($this->bezierMode) {
                $this->raylib->DrawLineBezier($startVec, $endVec, $thickness, $this->plotColor);
            } else {
                $this->raylib->DrawLineEx($startVec, $endVec, $thickness, $this->plotColor);
            }

            $prevX = $curX;
            $prevY = $curY;
        }
    }

    private function isMouseNearCurve(): bool
    {
        if (! $this->hovered) {
            return false;
        }

        $mouseX = (float) $this->raylib->GetMouseX();
        $mouseY = (float) $this->raylib->GetMouseY();

        return abs($mouseY - $this->evalAt($mouseX)) <= 8.0;
    }

    private function evalAt(float $screenX): float
    {
        $mathX = $this->grid->toMathX($screenX);
        $mathY = ($this->fn)($mathX, $this->t);
        $screenY = $this->grid->toScreenY($mathY);

        return max(-10000.0, min(10000.0, $screenY));
    }

    private function drawLabel(): void
    {
        $this->raylib->DrawText(
            $this->labelText,
            (int) $this->rect->x + 12,
            (int) $this->rect->y + $this->labelOffsetY,
            16,
            $this->plotColor,
        );
    }
}
