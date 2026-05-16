<?php

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\MouseClicked;
use Phpanim\Events\MouseScrolled;
use Phpanim\UI\Rect;
use Phpanim\UI\ScopedEventDispatcher;

// Rect covers (100, 100) → (300, 300)
beforeEach(function () {
    $this->rect = new Rect(100.0, 100.0, 200.0, 200.0);
    $this->global = new EventDispatcher;
    $this->scoped = new ScopedEventDispatcher($this->global, $this->rect);
});

// -------------------------------------------------------------------------
// Spatial filtering
// -------------------------------------------------------------------------

test('spatial event fires when cursor is inside the rect', function () {
    $called = 0;
    $this->scoped->on(Events::MouseClicked, function () use (&$called) {
        $called++;
    });

    $this->global->dispatch(Events::MouseClicked, new MouseClicked(150.0, 150.0, 0, 0.0));

    expect($called)->toBe(1);
});

test('spatial event is suppressed when cursor is outside the rect', function () {
    $called = 0;
    $this->scoped->on(Events::MouseClicked, function () use (&$called) {
        $called++;
    });

    $this->global->dispatch(Events::MouseClicked, new MouseClicked(50.0, 50.0, 0, 0.0));

    expect($called)->toBe(0);
});

test('spatial event fires on the rect boundary', function () {
    $called = 0;
    $this->scoped->on(Events::MouseClicked, function () use (&$called) {
        $called++;
    });

    $this->global->dispatch(Events::MouseClicked, new MouseClicked(100.0, 100.0, 0, 0.0)); // top-left
    $this->global->dispatch(Events::MouseClicked, new MouseClicked(300.0, 300.0, 0, 0.0)); // bottom-right

    expect($called)->toBe(2);
});

test('scroll event is spatially filtered', function () {
    $called = 0;
    $this->scoped->on(Events::MouseScrolled, function () use (&$called) {
        $called++;
    });

    $this->global->dispatch(Events::MouseScrolled, new MouseScrolled(200.0, 200.0, 0.0, 1.0, 0.0)); // inside
    $this->global->dispatch(Events::MouseScrolled, new MouseScrolled(50.0, 50.0, 0.0, 1.0, 0.0));   // outside

    expect($called)->toBe(1);
});

test('non-spatial events always fire regardless of mouse position', function () {
    $called = 0;
    $this->scoped->on(Events::KeyPressed, function () use (&$called) {
        $called++;
    });

    $this->global->dispatch(Events::KeyPressed, null);

    expect($called)->toBe(1);
});

// -------------------------------------------------------------------------
// Rect mutation is reflected live
// -------------------------------------------------------------------------

test('moving the rect changes which events are delivered', function () {
    $called = 0;
    $this->scoped->on(Events::MouseClicked, function () use (&$called) {
        $called++;
    });

    // (50, 50) is outside the original rect
    $this->global->dispatch(Events::MouseClicked, new MouseClicked(50.0, 50.0, 0, 0.0));
    expect($called)->toBe(0);

    // Slide the rect so (50, 50) is now inside
    $this->rect->x = 0.0;
    $this->rect->y = 0.0;

    $this->global->dispatch(Events::MouseClicked, new MouseClicked(50.0, 50.0, 0, 0.0));
    expect($called)->toBe(1);
});

// -------------------------------------------------------------------------
// dispose()
// -------------------------------------------------------------------------

test('dispose removes all scoped listeners from the global dispatcher', function () {
    $called = 0;

    $this->scoped->on(Events::MouseClicked, function () use (&$called) {
        $called++;
    });
    $this->scoped->on(Events::KeyPressed, function () use (&$called) {
        $called++;
    });

    $this->scoped->dispose();

    $this->global->dispatch(Events::MouseClicked, new MouseClicked(150.0, 150.0, 0, 0.0));
    $this->global->dispatch(Events::KeyPressed, null);

    expect($called)->toBe(0);
});

test('dispose does not remove unrelated listeners on the global dispatcher', function () {
    $called = 0;
    $this->global->on(Events::KeyPressed, function () use (&$called) {
        $called++;
    });

    $this->scoped->on(Events::KeyPressed, fn () => null);
    $this->scoped->dispose();

    $this->global->dispatch(Events::KeyPressed, null);

    expect($called)->toBe(1);
});

test('dispose can be called multiple times without error', function () {
    $this->scoped->on(Events::KeyPressed, fn () => null);

    expect(function () {
        $this->scoped->dispose();
        $this->scoped->dispose();
    })->not->toThrow(Throwable::class);
});
