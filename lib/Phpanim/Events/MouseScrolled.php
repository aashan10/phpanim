<?php

declare(strict_types=1);

namespace Phpanim\Events;

readonly class MouseScrolled
{
    public function __construct(
        public float $wheelX,
        public float $wheelY,
        public float $timestamp
    ) {}
}