<?php

use Phpanim\Animations\Builders\PhysicsBuilder;
use Tests\Support\MockRaylib;

beforeEach(function () {
    $this->raylib = (new MockRaylib())->setFrameTime(0.016);
    $this->target = (object) ['y' => 0.0];
    $this->parent = makeParent($this->raylib);
    $this->parent->target($this->target);
});

function physics(MockRaylib $raylib, object $target, $parent): PhysicsBuilder
{
    return (new PhysicsBuilder($parent, $raylib))->target($target);
}

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------

test('build throws when floor is not set', function () {
    $builder = physics($this->raylib, $this->target, $this->parent)
        ->property('y')->from(0.0)->gravity(980.0);

    expect(fn () => $builder->build())->toThrow(\InvalidArgumentException::class);
});

test('build returns a callable', function () {
    $closure = physics($this->raylib, $this->target, $this->parent)
        ->property('y')->from(0.0)->floor(500.0)
        ->build();

    expect($closure)->toBeCallable();
});

// -------------------------------------------------------------------------
// Animation correctness
// -------------------------------------------------------------------------

test('object falls to the floor with no bounce', function () {
    $closure = physics($this->raylib, $this->target, $this->parent)
        ->property('y')
        ->from(0.0)
        ->floor(500.0)
        ->gravity(980.0)
        ->bounce(0.0)
        ->build();

    runAnimation($closure);

    expect($this->target->y)->toBe(500.0);
});

test('object eventually settles at the floor with bounce', function () {
    // Use a small dt so gravity-per-frame stays below the 1.0 settling
    // threshold and discrete micro-bounce cycling cannot occur.
    $raylib = (new MockRaylib())->setFrameTime(0.001);

    $closure = physics($raylib, $this->target, makeParent($raylib)->target($this->target))
        ->property('y')
        ->from(0.0)
        ->floor(300.0)
        ->gravity(980.0)
        ->bounce(0.5)
        ->build();

    runAnimation($closure);

    expect($this->target->y)->toBe(300.0);
});

test('reads starting position from target when from is omitted', function () {
    $this->target->y = 0.0;

    $closure = physics($this->raylib, $this->target, $this->parent)
        ->property('y')
        ->floor(400.0)
        ->gravity(980.0)
        ->bounce(0.0)
        ->build();

    runAnimation($closure);

    expect($this->target->y)->toBe(400.0);
});

test('object starting at the floor settles immediately', function () {
    $closure = physics($this->raylib, $this->target, $this->parent)
        ->property('y')
        ->from(500.0)
        ->floor(500.0)
        ->gravity(980.0)
        ->bounce(0.0)
        ->build();

    runAnimation($closure);

    expect($this->target->y)->toBe(500.0);
});
