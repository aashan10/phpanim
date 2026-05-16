<?php

namespace Tests\Support;

use Phpanim\Raylib\Raylib;

/**
 * A test double for Raylib. Passes an empty FFI to the parent so readonly
 * property initialisation succeeds, then intercepts every __call with
 * configurable return values — no shared library or GPU required.
 */
class MockRaylib extends Raylib
{
    private float $frameTime = 0.016;
    private array $returns   = [];

    public function __construct()
    {
        parent::__construct(\FFI::cdef(''));
    }

    // -------------------------------------------------------------------------
    // Configuration helpers
    // -------------------------------------------------------------------------

    public function setFrameTime(float $dt): self
    {
        $this->frameTime = $dt;
        return $this;
    }

    public function returns(string $method, mixed $value): self
    {
        $this->returns[$method] = $value;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Override
    // -------------------------------------------------------------------------

    public function __call(string $name, array $params): mixed
    {
        if ($name === 'GetFrameTime') {
            return $this->frameTime;
        }

        return $this->returns[$name] ?? null;
    }
}
