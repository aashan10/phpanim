<?php

use Phpanim\Animations\Builders\WaitBuilder;
use Tests\Support\MockRaylib;

beforeEach(function () {
    $this->raylib = (new MockRaylib())->setFrameTime(0.5);
    $this->target = (object) [];
    $this->parent = makeParent($this->raylib);
    $this->parent->target($this->target);
});

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------

test('build returns a callable', function () {
    $closure = (new WaitBuilder($this->parent, $this->raylib))
        ->duration(1.0)
        ->build();

    expect($closure)->toBeCallable();
});

// -------------------------------------------------------------------------
// Animation correctness — dt=0.5 per frame
// -------------------------------------------------------------------------

test('terminates after accumulating the specified duration', function () {
    // dt=0.5, duration=1.0 → needs 2 frames to reach 1.0 seconds
    $closure = (new WaitBuilder($this->parent, $this->raylib))
        ->duration(1.0)
        ->build();

    // runAnimation throws if it never finishes — a successful call proves it terminates
    expect(fn () => runAnimation($closure))->not->toThrow(\RuntimeException::class);
});

test('longer duration requires more frames to complete', function () {
    $short = (new WaitBuilder($this->parent, $this->raylib))->duration(0.5)->build();
    $long  = (new WaitBuilder($this->parent, $this->raylib))->duration(2.0)->build();

    expect(runAnimation($short))->toBeLessThan(runAnimation($long));
});
