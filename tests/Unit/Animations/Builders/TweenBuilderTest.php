<?php

use Phpanim\Animations\Builders\TweenBuilder;
use Tests\Support\MockRaylib;

// dt=0.5, duration=1.0 → exactly 2 frames to reach t=1.0
beforeEach(function () {
    $this->raylib = (new MockRaylib())->setFrameTime(0.5);
    $this->target = (object) ['x' => 0.0];
    $this->parent = makeParent($this->raylib);
    $this->parent->target($this->target);
});

function tween(MockRaylib $raylib, object $target, $parent): TweenBuilder
{
    return (new TweenBuilder($parent, $raylib))->target($target);
}

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------

test('build throws when to is not set', function () {
    $builder = tween($this->raylib, $this->target, $this->parent)
        ->property('x')
        ->from(0.0);

    expect(fn () => $builder->build())->toThrow(\InvalidArgumentException::class);
});

test('build throws when from resolves to a non-float', function () {
    // from() never called and property has no float value
    $target  = (object) ['x' => 'not-a-float'];
    $builder = (new TweenBuilder($this->parent, $this->raylib))
        ->target($target)
        ->property('x')
        ->to(100.0);

    expect(fn () => $builder->build())->toThrow(\InvalidArgumentException::class);
});

test('build returns a callable', function () {
    $closure = tween($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->to(100.0)->duration(1.0)
        ->build();

    expect($closure)->toBeCallable();
});

// -------------------------------------------------------------------------
// Animation correctness
// -------------------------------------------------------------------------

test('linearly interpolates from 0 to 100 over 1 second', function () {
    $closure = tween($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->to(100.0)->duration(1.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(100.0);
});

test('reads initial value from the target when from is omitted', function () {
    $this->target->x = 40.0;

    $closure = tween($this->raylib, $this->target, $this->parent)
        ->property('x')->to(100.0)->duration(1.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(100.0);
});

test('applies a custom easing function', function () {
    // Constant easing of 0.5 means the value always snaps to the midpoint.
    // After the animation completes the final write is with easedT=1.0 (since
    // the tween clamps t to 1.0 on the last frame), so the final value is to.
    $closure = tween($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->to(100.0)->duration(1.0)
        ->easing(fn (float $t) => $t * $t) // ease-in quadratic
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(100.0);
});

test('resolves callable from and to at build time', function () {
    $closure = tween($this->raylib, $this->target, $this->parent)
        ->property('x')
        ->from(fn () => 10.0)
        ->to(fn () => 50.0)
        ->duration(1.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(50.0);
});

test('animates from a negative to a positive value', function () {
    $closure = tween($this->raylib, $this->target, $this->parent)
        ->property('x')->from(-100.0)->to(100.0)->duration(1.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(100.0);
});
