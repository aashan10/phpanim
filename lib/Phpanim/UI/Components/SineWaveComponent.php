<?php

declare(strict_types=1);

namespace Phpanim\UI\Components;

use Phpanim\Events\Events;
use Phpanim\Events\MouseClicked;
use Phpanim\UI\Component;

class SineWaveComponent extends Component
{
    private float $phase = 0.0;

    private float $frequency = 1.0; // radians per math unit

    private float $amplitude = 1.0; // math units

    private float $speed = 1.5; // radians per second

    private ?XYGridComponent $grid = null;

    private mixed $waveColor;

    private mixed $labelColor;

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function grid(XYGridComponent $grid): static
    {
        $this->grid = $grid;

        return $this;
    }

    public function frequency(float $frequency): static
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function amplitude(float $amplitude): static
    {
        $this->amplitude = $amplitude;

        return $this;
    }

    public function speed(float $speed): static
    {
        $this->speed = $speed;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    protected function onMount(): void
    {
        $this->waveColor = $this->raylib->GetColor(0x38BDF8FF);
        $this->labelColor = $this->raylib->GetColor(0x94A3B8FF);

        $this->dispatcher->on(Events::MouseClicked, function (MouseClicked $e): void {
            $this->phase = 0.0;
        });
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    protected function onRender(): void
    {
        if ($this->grid === null) {
            return;
        }

        $this->phase += $this->speed * (float) $this->raylib->GetFrameTime();

        $this->drawWave();
        $this->drawLabel();
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function drawWave(): void
    {
        // Sample one point per screen pixel across the grid's visible x range
        $screenXMin = (int) $this->rect->x;
        $screenXMax = (int) ($this->rect->x + $this->rect->width);

        $prevScreenX = $screenXMin;
        $prevScreenY = $this->mathToScreen($screenXMin);

        for ($screenX = $screenXMin + 1; $screenX <= $screenXMax; $screenX++) {
            $curScreenY = $this->mathToScreen($screenX);

            $this->raylib->DrawLine(
                $prevScreenX, $prevScreenY,
                $screenX, $curScreenY,
                $this->waveColor
            );

            $prevScreenX = $screenX;
            $prevScreenY = $curScreenY;
        }
    }

    private function mathToScreen(int $screenX): int
    {
        $mathX = $this->grid->toMathX((float) $screenX);
        $mathY = $this->amplitude * sin($this->frequency * $mathX + $this->phase);

        return (int) $this->grid->toScreenY($mathY);
    }

    private function drawLabel(): void
    {
        $rx = (int) $this->rect->x;
        $ry = (int) $this->rect->y;

        $this->raylib->DrawText(
            sprintf('sin(x)   φ = %.2f rad   click to reset', fmod($this->phase, M_PI * 2.0)),
            $rx + 12,
            $ry + 12,
            16,
            $this->labelColor
        );
    }
}
