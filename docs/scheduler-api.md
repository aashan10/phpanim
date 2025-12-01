# Scheduler API

The `Scheduler` class is a powerful tool for creating complex, time-based animations in PHPAnim. It uses PHP Fibers to manage non-blocking animation sequences, allowing you to define intricate animations in a clean, readable, and sequential way.

## Instantiating the Scheduler

To create a scheduler, you need two things:

- A `Raylib` instance.
- A target object whose properties you want to animate.

```php
use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;

class MyState {
    public float $x = 0;
    public float $y = 0;
    public float $alpha = 1.0;
}

$state = new MyState();
$scheduler = new Scheduler($rl, $state);
```

The scheduler will modify the public properties of the `$state` object over time.

## Core Concepts

The scheduler works by building a sequence of "animation blocks". Each method you call on the scheduler (like `tween`, `wait`, etc.) adds a new block to this sequence. When you call `start()` and `update()`, the scheduler executes these blocks one after another.

## Animation Methods

### `tween(string $property, float $from, float $to, float $duration)`

The `tween` method is the most fundamental animation block. It animates a single numeric property of the target object from a starting value to an ending value over a specified duration.

```php
// Animate the 'x' property from 0 to 500 over 3 seconds
$scheduler->tween('x', 0, 500, 3.0);
```

### `tweenMulti(float $duration, array $properties)`

Animates multiple properties of the target object simultaneously over the same duration. The `$properties` array should map property names to `[$from, $to]` value pairs.

```php
// Animate 'x' from 0 to 400 and 'y' from 50 to 300, both over 2.5 seconds
$scheduler->tweenMulti(2.5, [
    'x' => [0, 400],
    'y' => [50, 300],
]);
```

### `wait(float $duration)`

Pauses the animation sequence for a specified duration.

```php
// Wait for 2 seconds before executing the next animation
$scheduler->wait(2.0);
```

### `custom(callable $fn, float $duration)`

For more complex animations, the `custom` method allows you to define your own animation logic within a callable. The callable receives the target object, a progress value (from 0.0 to 1.0), and the delta time (`$dt`) for the current frame.

```php
// A custom animation that makes an object move in a circle
$scheduler->custom(function ($target, float $progress, float $dt) {
    $angle = $progress * 2 * M_PI; // 360 degrees
    $target->x = 400 + cos($angle) * 100;
    $target->y = 300 + sin($angle) * 100;
}, 5.0); // Run this custom logic for 5 seconds
```

## Composing Schedulers

### `parallel(array $schedulers)`

Executes multiple animation schedulers simultaneously. This is useful for creating complex scenes where different objects are animating independently at the same time.

```php
$scheduler1 = new Scheduler($rl, $obj1)->tween('x', 0, 100, 2);
$scheduler2 = new Scheduler($rl, $obj2)->tween('y', 0, 200, 3);

// The main scheduler will run both $scheduler1 and $scheduler2 in parallel
$mainScheduler = new Scheduler($rl, new stdClass());
$mainScheduler->parallel([$scheduler1, $scheduler2]);
```

### `then(Scheduler $scheduler)`

Executes another scheduler after the current scheduler's sequence has finished.

```php
$fadeIn = new Scheduler($rl, $obj)->tween('alpha', 0.0, 1.0, 1.0);
$move = new Scheduler($rl, $obj)->tween('x', 0, 100, 2.0);

// First, fade in the object, then move it
$fadeIn->then($move);
```

## Controlling Playback

### `start()`

Starts the animation sequence. This initializes the Fiber and gets it ready for execution. You must call `start()` before the `update()` method will have any effect.

### `update()`

This method should be called once per frame within your main game loop. It resumes the animation's Fiber, allowing the scheduler to advance the animation state for the current frame.

```php
// In your main game loop
while (!$rl->WindowShouldClose()) {
    $scheduler->update();
    
    $rl->BeginDrawing();
    // ... your drawing logic
    $rl->EndDrawing();
}
```

### `repeat()`

Makes the entire animation sequence loop indefinitely.

```php
$scheduler
    ->tween('x', 0, 100, 1)
    ->wait(0.5)
    ->tween('x', 100, 0, 1)
    ->repeat(); // The animation will loop forever
```

## Easing Functions

### `withEasing(callable $easingFunc)`

By default, all tweens are linear. You can apply an easing function to a scheduler to create more natural-looking motion. The easing function should be a callable that takes a float `$t` (from 0.0 to 1.0) and returns a modified float.

```php
function easeInOutCubic(float $t): float {
    return $t < 0.5 ? 4 * $t * $t * $t : 1 - pow(-2 * $t + 2, 3) / 2;
}

$scheduler
    ->withEasing('easeInOutCubic')
    ->tween('x', 0, 500, 3.0);
```

This easing will apply to all subsequent `tween` and `tweenMulti` calls on this scheduler instance.
