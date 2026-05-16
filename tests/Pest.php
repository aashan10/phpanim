<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something(): void
{
    // ..
}

/**
 * Run an animation closure inside a Fiber to completion and return the frame
 * count. Throws if the fiber doesn't terminate within $maxFrames.
 */
function runAnimation(callable $animation, int $maxFrames = 100_000): int
{
    $fiber  = new Fiber($animation);
    $frames = 1;
    $fiber->start();

    while ($fiber->isSuspended()) {
        if ($frames >= $maxFrames) {
            throw new \RuntimeException("Animation did not terminate within {$maxFrames} frames.");
        }
        $fiber->resume();
        $frames++;
    }

    return $frames;
}

/**
 * Build a minimal AnimationBuilder wired to the given MockRaylib so
 * individual builder tests don't have to repeat this boilerplate.
 */
function makeParent(\Tests\Support\MockRaylib $raylib): \Phpanim\Animations\Builders\AnimationBuilder
{
    return new \Phpanim\Animations\Builders\AnimationBuilder(
        $raylib,
        new \Phpanim\Events\EventDispatcher(),
        new \Phpanim\Animations\AnimationQueue(),
    );
}
