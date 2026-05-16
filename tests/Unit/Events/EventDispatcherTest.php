<?php

use Phpanim\Events\EventDispatcher;

test('on listener fires when event is dispatched', function () {
    $dispatcher = new EventDispatcher;
    $called = 0;

    $dispatcher->on('test.event', function () use (&$called) {
        $called++;
    });
    $dispatcher->dispatch('test.event');

    expect($called)->toBe(1);
});

test('dispatch passes data to listener', function () {
    $dispatcher = new EventDispatcher;
    $received = null;

    $dispatcher->on('test.event', function ($data) use (&$received) {
        $received = $data;
    });
    $dispatcher->dispatch('test.event', 'hello');

    expect($received)->toBe('hello');
});

test('multiple listeners for the same event all fire in order', function () {
    $dispatcher = new EventDispatcher;
    $log = [];

    $dispatcher->on('event', function () use (&$log) {
        $log[] = 'a';
    });
    $dispatcher->on('event', function () use (&$log) {
        $log[] = 'b';
    });
    $dispatcher->on('event', function () use (&$log) {
        $log[] = 'c';
    });
    $dispatcher->dispatch('event');

    expect($log)->toBe(['a', 'b', 'c']);
});

test('on listener fires on every dispatch', function () {
    $dispatcher = new EventDispatcher;
    $called = 0;

    $dispatcher->on('event', function () use (&$called) {
        $called++;
    });
    $dispatcher->dispatch('event');
    $dispatcher->dispatch('event');
    $dispatcher->dispatch('event');

    expect($called)->toBe(3);
});

test('once listener fires exactly once', function () {
    $dispatcher = new EventDispatcher;
    $called = 0;

    $dispatcher->once('event', function () use (&$called) {
        $called++;
    });
    $dispatcher->dispatch('event');
    $dispatcher->dispatch('event');
    $dispatcher->dispatch('event');

    expect($called)->toBe(1);
});

test('once listener is removed after firing', function () {
    $dispatcher = new EventDispatcher;

    $dispatcher->once('event', fn () => null);
    $dispatcher->dispatch('event');

    expect($dispatcher->hasListeners('event'))->toBeFalse();
});

test('off with a specific listener removes only that listener', function () {
    $dispatcher = new EventDispatcher;
    $log = [];

    $keep = function () use (&$log) {
        $log[] = 'keep';
    };
    $remove = function () use (&$log) {
        $log[] = 'remove';
    };

    $dispatcher->on('event', $keep);
    $dispatcher->on('event', $remove);
    $dispatcher->off('event', $remove);
    $dispatcher->dispatch('event');

    expect($log)->toBe(['keep']);
});

test('off without a listener removes all for that event', function () {
    $dispatcher = new EventDispatcher;
    $called = 0;

    $dispatcher->on('event', function () use (&$called) {
        $called++;
    });
    $dispatcher->once('event', function () use (&$called) {
        $called++;
    });
    $dispatcher->off('event');
    $dispatcher->dispatch('event');

    expect($called)->toBe(0);
});

test('off does not affect listeners on other events', function () {
    $dispatcher = new EventDispatcher;
    $called = 0;

    $dispatcher->on('other', function () use (&$called) {
        $called++;
    });
    $dispatcher->off('event');
    $dispatcher->dispatch('other');

    expect($called)->toBe(1);
});

test('hasListeners returns false when no listeners are registered', function () {
    $dispatcher = new EventDispatcher;

    expect($dispatcher->hasListeners('event'))->toBeFalse();
});

test('hasListeners returns true after on registration', function () {
    $dispatcher = new EventDispatcher;
    $dispatcher->on('event', fn () => null);

    expect($dispatcher->hasListeners('event'))->toBeTrue();
});

test('hasListeners returns true after once registration', function () {
    $dispatcher = new EventDispatcher;
    $dispatcher->once('event', fn () => null);

    expect($dispatcher->hasListeners('event'))->toBeTrue();
});

test('hasListeners returns false after all listeners are removed', function () {
    $dispatcher = new EventDispatcher;
    $dispatcher->on('event', fn () => null);
    $dispatcher->off('event');

    expect($dispatcher->hasListeners('event'))->toBeFalse();
});

test('dispatching an event with no listeners does not throw', function () {
    $dispatcher = new EventDispatcher;

    expect(fn () => $dispatcher->dispatch('ghost.event'))->not->toThrow(Throwable::class);
});
