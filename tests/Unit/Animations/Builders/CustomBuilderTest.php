<?php

use Phpanim\Animations\Builders\CustomBuilder;
use Tests\Support\MockRaylib;

beforeEach(function () {
    $this->raylib = (new MockRaylib())->setFrameTime(0.5);
    $this->target = (object) ['x' => 0.0];
    $this->parent = makeParent($this->raylib);
    $this->parent->target($this->target);
});

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------

test('build throws when no callback is provided', function () {
    $builder = (new CustomBuilder($this->parent, $this->raylib))
        ->target($this->target)
        ->duration(1.0);

    expect(fn () => $builder->build())->toThrow(\InvalidArgumentException::class);
});

test('build returns a callable', function () {
    $closure = (new CustomBuilder($this->parent, $this->raylib))
        ->target($this->target)
        ->callback(fn () => null)
        ->duration(1.0)
        ->build();

    expect($closure)->toBeCallable();
});

// -------------------------------------------------------------------------
// Animation correctness — dt=0.5, duration=1.0 → 2 frames
// -------------------------------------------------------------------------

test('callback receives the target object', function () {
    $received = null;

    $closure = (new CustomBuilder($this->parent, $this->raylib))
        ->target($this->target)
        ->callback(function ($target) use (&$received) { $received = $target; })
        ->duration(1.0)
        ->build();

    runAnimation($closure);

    expect($received)->toBe($this->target);
});

test('progress starts near 0 and ends at 1', function () {
    $progresses = [];

    $closure = (new CustomBuilder($this->parent, $this->raylib))
        ->target($this->target)
        ->callback(function ($target, $dt, float $progress) use (&$progresses) {
            $progresses[] = $progress;
        })
        ->duration(1.0)
        ->build();

    runAnimation($closure);

    expect(min($progresses))->toBeGreaterThan(0.0)
        ->and(max($progresses))->toBe(1.0);
});

test('callback is invoked for each frame of the duration', function () {
    $calls = 0;

    $closure = (new CustomBuilder($this->parent, $this->raylib))
        ->target($this->target)
        ->callback(function () use (&$calls) { $calls++; })
        ->duration(1.0)
        ->build();

    runAnimation($closure);

    // dt=0.5, duration=1.0 → 2 callback invocations
    expect($calls)->toBe(2);
});

test('animation terminates after the duration elapses', function () {
    $closure = (new CustomBuilder($this->parent, $this->raylib))
        ->target($this->target)
        ->callback(fn () => null)
        ->duration(2.0)
        ->build();

    expect(fn () => runAnimation($closure))->not->toThrow(\RuntimeException::class);
});
