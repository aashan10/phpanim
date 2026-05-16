<?php

declare(strict_types=1);

namespace Phpanim\UI;

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;

class ScopedEventDispatcher
{
    private const SPATIAL_EVENTS = [
        Events::MouseClicked,
        Events::MouseMoved,
        Events::MouseReleased,
        Events::MouseScrolled,
    ];

    private array $registrations = [];

    public function __construct(
        private EventDispatcher $global,
        private Rect $rect,
    ) {}

    public function on(string $event, callable $listener): void
    {
        if (in_array($event, self::SPATIAL_EVENTS, true)) {
            $wrapper = function ($data) use ($listener): void {
                if ($this->rect->contains($data->x, $data->y)) {
                    $listener($data);
                }
            };
        } else {
            $wrapper = $listener;
        }

        $this->global->on($event, $wrapper);
        $this->registrations[] = ['event' => $event, 'wrapper' => $wrapper];
    }

    public function dispose(): void
    {
        foreach ($this->registrations as ['event' => $event, 'wrapper' => $wrapper]) {
            $this->global->off($event, $wrapper);
        }

        $this->registrations = [];
    }
}
