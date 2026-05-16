<?php

declare(strict_types=1);

namespace Phpanim\Events;

readonly class KeyReleased
{
    public function __construct(
        public int $keycode,
        public string $keyName,
        public float $timestamp
    ) {}
}
