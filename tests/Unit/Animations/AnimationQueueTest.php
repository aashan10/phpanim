<?php

use Phpanim\Animations\AnimationQueue;

// -------------------------------------------------------------------------
// Basic lifecycle
// -------------------------------------------------------------------------

test('animation that completes immediately is removed after update', function () {
    $queue = new AnimationQueue;
    $ran = false;

    $queue->addAnimation('instant', function () use (&$ran) {
        $ran = true;
    });

    $queue->update();

    expect($ran)->toBeTrue()
        ->and($queue->isRunning('instant'))->toBeFalse();
});

test('suspended animation advances one step per update', function () {
    $queue = new AnimationQueue;
    $step = 0;

    // Two suspends so the first update() leaves it still running after step 2.
    $queue->addAnimation('step', function () use (&$step) {
        $step = 1;
        Fiber::suspend();
        $step = 2;
        Fiber::suspend();
        $step = 3;
    });

    $queue->update(); // start → step=1 → suspend → resume → step=2 → suspend
    expect($step)->toBe(2)
        ->and($queue->isRunning('step'))->toBeTrue();

    $queue->update(); // resume → step=3 → terminates
    expect($step)->toBe(3)
        ->and($queue->isRunning('step'))->toBeFalse();
});

test('isRunning returns false for an unknown key', function () {
    $queue = new AnimationQueue;

    expect($queue->isRunning('ghost'))->toBeFalse();
});

// -------------------------------------------------------------------------
// Interruptible animations
// -------------------------------------------------------------------------

test('queueing an interruptible animation while it runs replaces it', function () {
    $queue = new AnimationQueue;
    $log = [];

    $queue->addAnimation('move', function () use (&$log) {
        $log[] = 'first-start';
        Fiber::suspend();
        Fiber::suspend();
        $log[] = 'first-end'; // must not appear
    });

    $queue->update(); // start → first-start → suspend → resume → suspend (still alive)
    expect($log)->toBe(['first-start'])
        ->and($queue->isRunning('move'))->toBeTrue();

    $queue->addAnimation('move', function () use (&$log) {
        $log[] = 'second';
    });

    $queue->update(); // replaces first with second; first-end is never reached
    expect($log)->toContain('second')
        ->and($log)->not->toContain('first-end');
});

// -------------------------------------------------------------------------
// Non-interruptible animations
// -------------------------------------------------------------------------

test('queueing a non-interruptible animation while it runs is ignored', function () {
    $queue = new AnimationQueue;
    $log = [];

    $queue->addAnimation('locked', function () use (&$log) {
        $log[] = 'first';
        Fiber::suspend();
        $log[] = 'first-mid';
        Fiber::suspend();
        $log[] = 'first-end';
    }, false);

    $queue->update(); // start → first → suspend → resume → first-mid → suspend
    expect($log)->toBe(['first', 'first-mid'])
        ->and($queue->isRunning('locked'))->toBeTrue();

    $queue->addAnimation('locked', function () use (&$log) {
        $log[] = 'second'; // must not appear
    }, false);

    $queue->update(); // second is skipped; first continues to completion
    expect($log)->toBe(['first', 'first-mid', 'first-end'])
        ->and($log)->not->toContain('second');
});

// -------------------------------------------------------------------------
// interrupt() and clear()
// -------------------------------------------------------------------------

test('interrupt stops a running animation on the next update', function () {
    $queue = new AnimationQueue;

    $queue->addAnimation('x', function () {
        Fiber::suspend();
        Fiber::suspend();
    });

    $queue->update(); // start, advance once — still running
    expect($queue->isRunning('x'))->toBeTrue();

    $queue->interrupt('x');
    $queue->update();

    expect($queue->isRunning('x'))->toBeFalse();
});

test('clear removes all queued and running animations', function () {
    $queue = new AnimationQueue;

    $queue->addAnimation('a', function () {
        Fiber::suspend();
        Fiber::suspend();
    });
    $queue->update();
    expect($queue->isRunning('a'))->toBeTrue();

    $queue->clear();

    expect($queue->isRunning('a'))->toBeFalse();
});

test('clear also discards animations waiting in the queue', function () {
    $queue = new AnimationQueue;
    $ran = false;

    $queue->addAnimation('pending', function () use (&$ran) {
        $ran = true;
    });

    $queue->clear();
    $queue->update(); // queue is empty, nothing should run

    expect($ran)->toBeFalse();
});

// -------------------------------------------------------------------------
// addImmediateAnimation()
// -------------------------------------------------------------------------

test('addImmediateAnimation starts the fiber before update is called', function () {
    $queue = new AnimationQueue;
    $started = false;

    $queue->addImmediateAnimation('now', function () use (&$started) {
        $started = true;
        Fiber::suspend();
    });

    // No update() called yet
    expect($started)->toBeTrue()
        ->and($queue->isRunning('now'))->toBeTrue();
});

test('addImmediateAnimation interrupts a running animation with the same key', function () {
    $queue = new AnimationQueue;
    $log = [];

    $queue->addImmediateAnimation('fx', function () use (&$log) {
        $log[] = 'first';
        Fiber::suspend();
        $log[] = 'first-continued'; // must not appear
    });

    expect($log)->toBe(['first']);

    $queue->addImmediateAnimation('fx', function () use (&$log) {
        $log[] = 'second';
        Fiber::suspend();
    });

    $queue->update(); // should resume second, not first
    expect($log)->toContain('second')
        ->and($log)->not->toContain('first-continued');
});
