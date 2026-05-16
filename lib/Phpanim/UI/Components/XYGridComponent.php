<?php

declare(strict_types=1);

namespace Phpanim\UI\Components;

use Phpanim\UI\Component;

class XYGridComponent extends Component
{
    private float $pixelsPerUnit = 80.0;

    private mixed $bgColor;

    private mixed $gridColor;

    private mixed $axisColor;

    private mixed $labelColor;

    private mixed $borderColor;

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function pixelsPerUnit(float $ppu): static
    {
        $this->pixelsPerUnit = $ppu;

        return $this;
    }

    /**
     * Add a child component and synchronise its rect to fill the grid's plot
     * area so that its spatial event filtering aligns with what is drawn.
     * Call bounds() on the grid before addPlot().
     */
    public function addPlot(Component $component): static
    {
        $component->bounds(
            $this->rect->x,
            $this->rect->y,
            $this->rect->width,
            $this->rect->height,
        );

        $this->addChild($component);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Coordinate transform (public so child plots can use them)
    // -------------------------------------------------------------------------

    public function toScreenX(float $mathX): float
    {
        return $this->rect->x + $this->rect->width / 2.0 + $mathX * $this->pixelsPerUnit;
    }

    public function toScreenY(float $mathY): float
    {
        // Screen Y increases downward; mathematical Y increases upward.
        return $this->rect->y + $this->rect->height / 2.0 - $mathY * $this->pixelsPerUnit;
    }

    public function toMathX(float $screenX): float
    {
        return ($screenX - ($this->rect->x + $this->rect->width / 2.0)) / $this->pixelsPerUnit;
    }

    public function toMathY(float $screenY): float
    {
        return (($this->rect->y + $this->rect->height / 2.0) - $screenY) / $this->pixelsPerUnit;
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    protected function onMount(): void
    {
        $this->bgColor = $this->raylib->GetColor(0x0D0D1AFF);
        $this->gridColor = $this->raylib->GetColor(0x1E1E3FFF);
        $this->axisColor = $this->raylib->GetColor(0x4A4A7AFF);
        $this->labelColor = $this->raylib->GetColor(0x6B7280FF);
        $this->borderColor = $this->raylib->GetColor(0x2D2D5AFF);
    }

    // -------------------------------------------------------------------------
    // Render — draws before children so plots paint on top of the grid
    // -------------------------------------------------------------------------

    protected function onRender(): void
    {
        $rx = (int) $this->rect->x;
        $ry = (int) $this->rect->y;
        $rw = (int) $this->rect->width;
        $rh = (int) $this->rect->height;

        // Background
        $this->raylib->DrawRectangle($rx, $ry, $rw, $rh, $this->bgColor);

        $this->drawGrid($rx, $ry, $rw, $rh);
        $this->drawAxes($rx, $ry, $rw, $rh);
        $this->drawTickLabels($rx, $ry, $rw, $rh);

        $this->raylib->DrawRectangleLines($rx, $ry, $rw, $rh, $this->borderColor);
    }

    // -------------------------------------------------------------------------
    // Private drawing helpers
    // -------------------------------------------------------------------------

    private function drawGrid(int $rx, int $ry, int $rw, int $rh): void
    {
        // Vertical lines at each integer x unit within the visible range
        $xUnitMin = (int) ceil($this->toMathX($rx));
        $xUnitMax = (int) floor($this->toMathX($rx + $rw));

        for ($u = $xUnitMin; $u <= $xUnitMax; $u++) {
            $lx = (int) $this->toScreenX($u);
            $this->raylib->DrawLine($lx, $ry, $lx, $ry + $rh, $this->gridColor);
        }

        // Horizontal lines at each integer y unit within the visible range
        // Screen bottom maps to negative math-y, screen top maps to positive math-y
        $yUnitMin = (int) ceil($this->toMathY($ry + $rh));
        $yUnitMax = (int) floor($this->toMathY($ry));

        for ($u = $yUnitMin; $u <= $yUnitMax; $u++) {
            $ly = (int) $this->toScreenY($u);
            $this->raylib->DrawLine($rx, $ly, $rx + $rw, $ly, $this->gridColor);
        }
    }

    private function drawAxes(int $rx, int $ry, int $rw, int $rh): void
    {
        // X-axis (y = 0 in math space)
        $axisY = (int) $this->toScreenY(0);
        if ($axisY >= $ry && $axisY <= $ry + $rh) {
            $this->raylib->DrawLine($rx, $axisY, $rx + $rw, $axisY, $this->axisColor);
        }

        // Y-axis (x = 0 in math space)
        $axisX = (int) $this->toScreenX(0);
        if ($axisX >= $rx && $axisX <= $rx + $rw) {
            $this->raylib->DrawLine($axisX, $ry, $axisX, $ry + $rh, $this->axisColor);
        }
    }

    private function drawTickLabels(int $rx, int $ry, int $rw, int $rh): void
    {
        $axisY = (int) max($ry + 2, min($ry + $rh - 16, $this->toScreenY(0)));
        $axisX = (int) max($rx + 2, min($rx + $rw - 20, $this->toScreenX(0)));

        $xUnitMin = (int) ceil($this->toMathX($rx));
        $xUnitMax = (int) floor($this->toMathX($rx + $rw));

        // Labels along the X-axis
        for ($u = $xUnitMin; $u <= $xUnitMax; $u++) {
            if ($u === 0) {
                continue;
            }
            $lx = (int) $this->toScreenX($u);
            // Small tick on the axis
            $this->raylib->DrawLine($lx, $axisY - 3, $lx, $axisY + 3, $this->axisColor);
            $this->raylib->DrawText((string) $u, $lx - 4, $axisY + 6, 12, $this->labelColor);
        }

        $yUnitMin = (int) ceil($this->toMathY($ry + $rh));
        $yUnitMax = (int) floor($this->toMathY($ry));

        // Labels along the Y-axis
        for ($u = $yUnitMin; $u <= $yUnitMax; $u++) {
            if ($u === 0) {
                continue;
            }
            $ly = (int) $this->toScreenY($u);
            // Small tick on the axis
            $this->raylib->DrawLine($axisX - 3, $ly, $axisX + 3, $ly, $this->axisColor);
            $this->raylib->DrawText((string) $u, $axisX + 6, $ly - 6, 12, $this->labelColor);
        }

        // Origin label
        $this->raylib->DrawText('0', $axisX + 4, $axisY + 4, 12, $this->labelColor);
    }
}
