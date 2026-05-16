<?php

use Phpanim\Animations\Builders\InertiaBuilder;
use Tests\Support\MockRaylib;

beforeEach(function () {
    $this->raylib = (new MockRaylib())->setFrameTime(0.016);
    $this->target = (object) ['x' => 0.0];
    $this->parent = makeParent($this->raylib);
    $this->parent->target($this->target);
});

function inertia(MockRaylib $raylib, object $target, $parent): InertiaBuilder
{
    return (new InertiaBuilder($parent, $raylib))->target($target);
}

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------

test('build throws when from resolves to a non-float', function () {
    $target  = (object) ['x' => 'not-a-float'];
    $builder = (new InertiaBuilder($this->parent, $this->raylib))
        ->target($target)
        ->property('x');

    expect(fn () => $builder->build())->toThrow(\InvalidArgumentException::class);
});

test('build returns a callable', function () {
    $closure = inertia($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(100.0)
        ->build();

    expect($closure)->toBeCallable();
});

// -------------------------------------------------------------------------
// Animation correctness
// -------------------------------------------------------------------------

test('position moves in the direction of the initial velocity', function () {
    $closure = inertia($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(200.0)->friction(0.5)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBeGreaterThan(0.0);
});

test('position moves backward with a negative initial velocity', function () {
    $closure = inertia($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(-200.0)->friction(0.5)
        ->build();

    runAnimation($closure);

    expect($this->target->x)->toBeLessThan(0.0);
});

test('animation terminates — velocity decays to threshold', function () {
    $closure = inertia($this->raylib, $this->target, $this->parent)
        ->property('x')->from(0.0)->velocity(100.0)->friction(0.9)
        ->build();

    // runAnimation throws if it never terminates within the frame cap
    expect(fn () => runAnimation($closure))->not->toThrow(\RuntimeException::class);
});

test('higher friction decays to a smaller total displacement', function () {
    $lowFriction  = (object) ['x' => 0.0];
    $highFriction = (object) ['x' => 0.0];

    runAnimation(
        (new InertiaBuilder($this->parent, $this->raylib))
            ->target($lowFriction)->property('x')->from(0.0)->velocity(200.0)->friction(0.3)
            ->build()
    );

    runAnimation(
        (new InertiaBuilder($this->parent, $this->raylib))
            ->target($highFriction)->property('x')->from(0.0)->velocity(200.0)->friction(0.9)
            ->build()
    );

    expect($highFriction->x)->toBeLessThan($lowFriction->x);
});
