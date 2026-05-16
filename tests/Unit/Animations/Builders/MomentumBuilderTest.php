<?php

use Phpanim\Animations\Builders\MomentumBuilder;
use Tests\Support\MockRaylib;

beforeEach(function () {
    // dt=0.1 makes the arithmetic clean: deceleration = friction * 0.1
    $this->raylib = (new MockRaylib())->setFrameTime(0.1);
    $this->target = (object) ['x' => 0.0];
    $this->parent = makeParent($this->raylib);
    $this->parent->target($this->target);
});

function momentum(MockRaylib $raylib, object $target, $parent): MomentumBuilder
{
    return (new MomentumBuilder($parent, $raylib))->target($target);
}

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------

test('build throws when from resolves to a non-float', function () {
    $target  = (object) ['x' => 'bad'];
    $builder = (new MomentumBuilder($this->parent, $this->raylib))
        ->target($target)->property('x');

    expect(fn () => $builder->build())->toThrow(\InvalidArgumentException::class);
});

test('build returns a callable', function () {
    $closure = momentum($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(100.0)->friction(500.0)
        ->build();

    expect($closure)->toBeCallable();
});

// -------------------------------------------------------------------------
// Animation correctness — dt=0.1, friction=500 → decel=50/frame
// -------------------------------------------------------------------------

test('position moves in the direction of velocity', function () {
    $closure = momentum($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(100.0)->friction(500.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBeGreaterThan(0.0);
});

test('object moving with negative velocity ends up behind start', function () {
    $closure = momentum($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(-100.0)->friction(500.0)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBeLessThan(0.0);
});

test('animation terminates — velocity is reduced to zero', function () {
    $closure = momentum($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(100.0)->friction(500.0)
        ->build();

    expect(fn () => runAnimation($closure))->not->toThrow(\RuntimeException::class);
});

test('higher friction produces less total displacement', function () {
    $slow = (object) ['x' => 0.0];
    $fast = (object) ['x' => 0.0];

    runAnimation(
        (new MomentumBuilder($this->parent, $this->raylib))
            ->target($slow)->property('x')->from(0.0)->velocity(100.0)->friction(100.0)
            ->build()
    );

    runAnimation(
        (new MomentumBuilder($this->parent, $this->raylib))
            ->target($fast)->property('x')->from(0.0)->velocity(100.0)->friction(1000.0)
            ->build()
    );

    expect($fast->x)->toBeLessThan($slow->x);
});
