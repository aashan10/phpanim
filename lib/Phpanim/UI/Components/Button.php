<?php

declare(strict_types=1);

namespace Phpanim\UI\Components;

use Phpanim\Events\Events;
use Phpanim\Events\MouseClicked;
use Phpanim\UI\Component;

class Button extends Component
{
    private string $text = '';

    private mixed $onClick = null;

    private mixed $bgColor;

    private mixed $bgHoverColor;

    private mixed $bgPressColor;

    private mixed $textColor;

    private mixed $borderColor;

    private mixed $borderHoverColor;

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function label(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function onClick(callable $callback): static
    {
        $this->onClick = $callback;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    protected function onMount(): void
    {
        $this->bgColor = $this->raylib->GetColor(0x1E293BFF);
        $this->bgHoverColor = $this->raylib->GetColor(0x334155FF);
        $this->bgPressColor = $this->raylib->GetColor(0x0F172AFF);
        $this->textColor = $this->raylib->GetColor(0xF8FAFCFF);
        $this->borderColor = $this->raylib->GetColor(0x475569FF);
        $this->borderHoverColor = $this->raylib->GetColor(0x94A3B8FF);

        $this->dispatcher->on(Events::MouseClicked, function (MouseClicked $e): void {
            if ($this->onClick !== null) {
                ($this->onClick)();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    protected function onRender(): void
    {
        $rx = (int) $this->rect->x;
        $ry = (int) $this->rect->y;
        $rw = (int) $this->rect->width;
        $rh = (int) $this->rect->height;

        $bg = $this->pressed ? $this->bgPressColor
                : ($this->hovered ? $this->bgHoverColor : $this->bgColor);
        $border = $this->hovered ? $this->borderHoverColor : $this->borderColor;

        $this->raylib->DrawRectangle($rx, $ry, $rw, $rh, $bg);
        $this->raylib->DrawRectangleLines($rx, $ry, $rw, $rh, $border);

        $fontSize = 18;
        $textWidth = $this->raylib->MeasureText($this->text, $fontSize);
        $textX = $rx + (int) (($rw - $textWidth) / 2);
        $textY = $ry + (int) (($rh - $fontSize) / 2);

        $this->raylib->DrawText($this->text, $textX, $textY, $fontSize, $this->textColor);
    }
}
