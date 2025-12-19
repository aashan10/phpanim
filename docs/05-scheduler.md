# 5. The Scheduler

The `Scheduler` is the most powerful and unique feature of PHPAnim. It is a time-based animation system built on **PHP Fibers** that allows you to define complex animation sequences in a simple, clean, and sequential manner, avoiding "callback hell" or complex state machines.

## How it Works

When you build an animation sequence with the scheduler, you are creating a series of steps. When you call `$scheduler->start()`, it creates a Fiber that encapsulates this sequence.

Each time you call `$scheduler->update()` in your main loop, the scheduler resumes the Fiber. The Fiber executes its logic just long enough for the current frame (e.g., updating a value in a `tween`), and then **suspends** itself, yielding control back to your main loop. This happens on every frame, creating smooth animation.

## Instantiation

A scheduler needs two things to work:

1.  A `Raylib` instance (for timekeeping).
2.  A target `object` whose public properties it will animate.

```php
$state = new class {
    public float $x = 0;
    public float $y = 0;
};

$scheduler = new Scheduler($rl, $state);
```

## Building Animation Sequences

You can chain the following methods to build your animation.

### `tween()`

Animates a single numeric property from a start value to an end value over a duration.

```php
// tween(string $property, float $from, float $to, float $duration)
$scheduler->tween('x', 0, 100, 2.0); // Animate state->x from 0 to 100 in 2s
```

### `tweenMulti()`

Animates multiple properties at the same time.

```php
// tweenMulti(float $duration, array $properties)
$scheduler->tweenMulti(2.0, [
    'x' => [0, 100],   // Animate state->x from 0 to 100
    'y' => [50, 250],  // Animate state->y from 50 to 250
]);
```

### `wait()`

Pauses the sequence for a given number of seconds.

```php
// wait(float $duration)
$scheduler->wait(1.5); // Do nothing for 1.5s
```

### `custom()`

Provides a callback for custom animation logic. The callback receives the target object, a progress value (0.0 to 1.0), and the frame's delta time.

```php
// custom(callable $callback, float $duration)
$scheduler->custom(function ($target, float $progress, float $dt) {
    $angle = $progress * M_PI * 2;
    $target->x = cos($angle) * 100;
    $target->y = sin($angle) * 100;
}, 5.0); // Run for 5s
```

### `repeat()`

Loops the entire sequence from the beginning.

```php
$scheduler
    ->tween('x', 0, 100, 1)
    ->wait(0.5)
    ->tween('x', 100, 0, 1)
    ->repeat(); // Will loop forever
```

## Controlling Playback

A scheduler won't do anything until you control it.

-   `start()`: Prepares the scheduler and its Fiber for execution. **Must be called once** before the animation begins.
-   `update()`: Advances the animation by one frame. **Must be called on every frame** in your `update()` method.

```php
// In Scene::load()
$this->scheduler->start();

// In Scene::update()
$this->scheduler->update();
```

## Advanced Composition

You can compose schedulers for more complex, parallel animations.

### `parallel()`

The `parallel()` method runs an array of other schedulers at the same time. The `parallel` block is considered finished when **all** of the child schedulers have completed their animations.

This is extremely useful for animating different objects independently.

```php
// Animate two different objects at the same time
$state1 = new class { public float $x = 0; };
$state2 = new class { public float $y = 0; };

$scheduler1 = (new Scheduler($rl, $state1))->tween('x', 0, 100, 2);
$scheduler2 = (new Scheduler($rl, $state2))->tween('y', 0, 200, 3);

// The main scheduler will manage both
$masterScheduler = new Scheduler($rl, new stdClass());
$masterScheduler->parallel([$scheduler1, $scheduler2]);

// Start the master scheduler
$masterScheduler->start();

// In your update loop, you only need to update the master
$masterScheduler->update();
```

### `then(Scheduler $scheduler)`

Executes another scheduler after the current scheduler's sequence has finished. This is useful for chaining complex animation sequences where one must complete before the next begins.

```php
// Fade in an object, then move it
$fadeIn = new Scheduler($rl, $obj)->tween('alpha', 0.0, 1.0, 1.0);
$move = new Scheduler($rl, $obj)->tween('x', 0, 100, 2.0);

$fadeIn->then($move)->start(); // `move` will start only after `fadeIn` is complete
```

---

[**Next: Built-in Visualizations **](./06-visualizations.md)
