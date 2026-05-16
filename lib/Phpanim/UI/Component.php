<?php

declare(strict_types=1);

namespace Phpanim\UI;

use Phpanim\Events\EventDispatcher;
use Phpanim\Raylib\Raylib;

abstract class Component implements ComponentInterface
{
    protected Rect $rect;

    protected ScopedEventDispatcher $dispatcher;

    protected bool $hovered = false;

    protected bool $pressed = false;

    private array $children = [];

    private bool $mounted = false;

    // Tracks the active scissor stack so nested components restore the parent's
    // clip rect when they finish rather than disabling scissoring entirely.
    private static array $scissorStack = [];

    public function __construct(
        protected Raylib $raylib,
        EventDispatcher $globalDispatcher,
    ) {
        // Rect and ScopedEventDispatcher share the same Rect instance so that
        // mutations to $this->rect (e.g. via animations) are reflected in
        // spatial event filtering without any extra wiring.
        $this->rect = new Rect(0.0, 0.0, 0.0, 0.0);
        $this->dispatcher = new ScopedEventDispatcher($globalDispatcher, $this->rect);
    }

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function getRect(): Rect
    {
        return $this->rect;
    }

    public function bounds(float $x, float $y, float $width, float $height): static
    {
        $this->rect->x = $x;
        $this->rect->y = $y;
        $this->rect->width = $width;
        $this->rect->height = $height;

        return $this;
    }

    public function addChild(Component $child): static
    {
        $this->children[] = $child;

        if ($this->mounted) {
            $child->mount();
        }

        return $this;
    }

    public function children(Component ...$components): static
    {
        foreach ($components as $component) {
            $this->addChild($component);
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Lifecycle — final so the cascade to children is always guaranteed
    // -------------------------------------------------------------------------

    final public function mount(): void
    {
        $this->onMount();

        foreach ($this->children as $child) {
            $child->mount();
        }

        $this->mounted = true;
    }

    final public function destroy(): void
    {
        foreach ($this->children as $child) {
            $child->destroy();
        }

        $this->onDestroy();
        $this->dispatcher->dispose();

        $this->children = [];
        $this->mounted = false;
    }

    // -------------------------------------------------------------------------
    // Render — final so hover/pressed state and child rendering are always
    // handled uniformly regardless of what the subclass does in onRender()
    // -------------------------------------------------------------------------

    final public function render(): void
    {
        $x = (float) $this->raylib->GetMouseX();
        $y = (float) $this->raylib->GetMouseY();

        $this->hovered = $this->rect->contains($x, $y);
        $this->pressed = $this->hovered && (bool) $this->raylib->IsMouseButtonDown(0);

        $clip = [
            (int) $this->rect->x,
            (int) $this->rect->y,
            (int) $this->rect->width,
            (int) $this->rect->height,
        ];

        self::$scissorStack[] = $clip;
        $this->raylib->BeginScissorMode(...$clip);

        $this->onRender();

        foreach ($this->children as $child) {
            $child->render();
        }

        array_pop(self::$scissorStack);

        // Restore the parent's scissor rect, or end scissoring if this was
        // the outermost component.
        if (! empty(self::$scissorStack)) {
            $this->raylib->BeginScissorMode(...end(self::$scissorStack));
        } else {
            $this->raylib->EndScissorMode();
        }
    }

    // -------------------------------------------------------------------------
    // Override points
    // -------------------------------------------------------------------------

    protected function onMount(): void {}

    protected function onDestroy(): void {}

    abstract protected function onRender(): void;
}
