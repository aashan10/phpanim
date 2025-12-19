# 6. Built-in Visualizations

PHPAnim includes high-level "Visualization" components that encapsulate complex drawing logic, allowing you to create sophisticated graphics with very little code. You can use these inside your scenes to build your application.

## `XYGrid`

The `XYGrid` class draws a complete Cartesian grid, including axes, grid lines, ticks, and labels. It is highly customizable.

### Usage

Instantiate `XYGrid` in your scene's `load()` or `__construct()` method, and then call its `render()` method inside `update()`.

```php
// In your Scene class

private XYGrid $grid;

public function load(Raylib $rl): void
{
    $this->grid = new XYGrid(
        origin: new Vec2($rl->GetScreenWidth() / 2, $rl->GetScreenHeight() / 2),
        spacing: 25.0,
        unitSize: 25.0,
        labelInterval: 4
    );
}

public function update(Raylib $rl): void
{
    $rl->ClearBackground(...);
    $this->grid->render($rl); // Draws the grid
}
```

### Constructor Parameters

```php
public function __construct(
    public Vec2 $origin,              // The (x, y) screen coordinates of the grid's origin.
    public float $spacing = 20.0,     // The pixel distance between each grid line.
    public float $unitSize = 20.0,    // How many pixels represent one logical unit for labels (e.g., 20px = 1 unit).
    public int $labelInterval = 5,    // Draw a label every Nth grid line.
    public int $gridColor = 0xEEEEEEFF,        // Color of the minor grid lines.
    public int $majorGridColor = 0xCCCCCCFF,   // Color of the major grid lines (where labels appear).
    public int $axisColor = 0x000000FF,        // Color of the X and Y axes.
    public int $labelColor = 0x333333FF,       // Color of the number labels.
    public int $fontSize = 10,                 // Font size for the labels.
    public bool $showLabels = true,            // Master toggle for all labels.
    public bool $showTicks = true,             // Master toggle for ticks on the axes.
    public float $tickSize = 5.0,              // The length of the ticks in pixels.
    public int $labelPrecision = 0,            // Number of decimal places for labels.
) {}
```

## `Curve`

The `Curve` class draws a smooth curve based on a mathematical function. It takes a PHP `Closure` (`y = f(x)`) and renders it within a given set of bounds.

### Usage

The power of `Curve` is that its properties can be animated by the `Scheduler`. By tweening the `min` and `max` bounds, you can create the effect of a function being drawn over time.

```php
// In your Scene class

private Curve $sineWave;

public function load(Raylib $rl): void
{
    $origin = new Vec2($rl->GetScreenWidth() / 2, $rl->GetScreenHeight() / 2);

    $this->sineWave = new Curve(
        origin: $origin,
        min: new Vec2(-10, -2), // Start drawing from x = -10
        max: new Vec2(-10, 2),  // Animate this value to draw the curve
        fn: function (float $x): float {
            return sin($x);
        },
        color: 0xFF0000FF,
        unitSize: 50.0
    );

    // Animate the maximum X bound of the curve to "draw" it across the screen
    $this->scheduler = new Scheduler($rl, $this->sineWave->max);
    $this->scheduler->tween('x', -10, 10, 5.0)->start();
}

public function update(Raylib $rl): void
{
    $this->scheduler->update();
    $this->grid->render($rl);
    $this->sineWave->render($rl); // Draws the curve
}
```

### Constructor Parameters

```php
public function __construct(
    public Vec2 $origin,         // The (x, y) screen coordinates of the graph's origin.
    public Vec2 $min,            // The minimum {x, y} bounds in logical graph units.
    public Vec2 $max,            // The maximum {x, y} bounds in logical graph units.
    public Closure $fn,           // The function to plot, must be `function(float $x): float`.
    public int $color = 0xFF0000FF,       // Color of the curve.
    public float $thickness = 2.0,       // Thickness of the curve.
    public float $unitSize = 20.0,       // How many pixels represent one logical unit. Must match your grid.
    public int $segments = 100            // Number of line segments used to approximate the curve (smoothness).
) {}
```

---

[**Next: Using the Raylib FFI Layer **](./07-raylib-ffi.md)
