<?php

use Phpanim\Animations\Builders\SpringBuilder;
use Tests\Support\MockRaylib;

beforeEach(function () {
    $this->raylib = (new MockRaylib())->setFrameTime(0.016);
    $this->target = (object) ['x' => 0.0];
    $this->parent = makeParent($this->raylib);
    $this->parent->target($this->target);
});

function spring(MockRaylib $raylib, object $target, $parent): SpringBuilder
{
    return (new SpringBuilder($parent, $raylib))->target($target);
}

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------

test('build throws when to is not set', function () {
    $builder = spring($this->raylib, $this->target, $this->parent)
        ->property('x')
        ->from(0.0);

    expect(fn () => $builder->build())->toThrow(\InvalidArgumentException::class);
});

test('build returns a callable', function () {
    $closure = spring($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->to(100.0)
        ->build();

    expect($closure)->toBeCallable();
});

// -------------------------------------------------------------------------
// Animation correctness
// -------------------------------------------------------------------------

test('converges to the target value', function () {
    $closure = spring($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->to(100.0)
        ->stiffness(200.0)->damping(20.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(100.0);
});

test('spring settles at target regardless of starting position', function () {
    $this->target->x = 500.0;

    $closure = spring($this->raylib, $this->target, $this->parent)
        ->property('x')->from(500.0)->to(0.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(0.0);
});

test('reads initial value from target when from is omitted', function () {
    $this->target->x = 50.0;

    $closure = spring($this->raylib, $this->target, $this->parent)
        ->property('x')->to(200.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(200.0);
});

test('initial velocity shifts the trajectory', function () {
    // With a large initial velocity toward the target the spring should still
    // settle — just verify it terminates and lands on the target.
    $closure = spring($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->to(100.0)
        ->velocity(500.0)
        ->stiffness(200.0)->damping(25.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBe(100.0);
});
