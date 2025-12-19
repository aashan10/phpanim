# 7. Using the Raylib FFI Layer

At its core, PHPAnim is a wrapper around the native Raylib library, made possible by PHP's **Foreign Function Interface (FFI)**. While PHPAnim provides high-level abstractions like the Scheduler and Visualizations, you have full power to call any Raylib function directly.

## The `Raylib` Class

The `Aashan\Phpanim\Raylib` class is your gateway to the C library. The `render` command automatically creates an instance of this class and passes it to your scene's lifecycle methods (`load`, `update`, etc.).

### How it Works

When the application starts, it loads the Raylib shared library (`.so`, `.dll`, or `.dylib`) and parses a C header definition. This tells FFI which functions and data structures are available. The `Raylib` class then uses magic methods (`__call`) to expose all the defined C functions as if they were native PHP methods.

## Calling Raylib Functions

You can call any Raylib function on the `$rl` object passed to your scene. The function names and arguments should match the C API.

```php
// This C code:
// DrawText("Hello World", 100, 100, 20, BLACK);

// Becomes this PHP code:
$black = $rl->struct('Color', ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255]);
$rl->DrawText("Hello World", 100, 100, 20, $black);
```

You can find a complete list of all Raylib functions on the [Raylib Cheat Sheet](https://www.raylib.com/cheatsheet/cheatsheet.html).

> **Note:** Before you can call a function, it **must** be declared in the header definition file provided to the application. PHPAnim ships with a `raylib.h` that contains most common functions, but you can provide your own if you need more.

## Working with C Structs

Raylib uses many C `struct`s to pass data around (e.g., `Color`, `Vector2`, `Rectangle`). The `$rl->struct()` method makes it easy to create and manage these from PHP.

### Creating Structs

The `struct()` method takes the struct name and an optional associative array of initial values. It returns a `FFI\CData` object that represents the C struct in PHP.

```php
// Creating a Vector2 for a player's position
$playerPos = $rl->struct('Vector2', [
    'x' => 150.5,
    'y' => 300.0
]);

// Creating a Color
$magenta = $rl->struct('Color', [
    'r' => 255, 'g' => 0, 'b' => 255, 'a' => 255
]);
```

### Reading and Writing Struct Fields

You can access and modify the fields of a `FFI\CData` object just like a normal PHP object.

```php
// Read a value
$currentX = $playerPos->x;

// Modify a value
$playerPos->x += 50.0; // Move the player right
```

### Passing Structs to Functions

You can pass these struct objects directly to Raylib functions that expect them.

```php
// C function: void DrawCircleV(Vector2 center, float radius, Color color);
$rl->DrawCircleV($playerPos, 25.0, $magenta);
```

## `GetColor()` Helper

Instead of creating a new `Color` struct every time from a hex value, you can use the convenient `$rl->GetColor(0xRRGGBBAA)` helper. PHPAnim automatically registers this helper for you.

```php
// Instead of this:
$color = $rl->struct('Color', ['r' => 128, 'g' => 128, 'b' => 128, 'a' => 255]);
$rl->ClearBackground($color);

// You can do this:
$rl->ClearBackground($rl->GetColor(0x808080FF));
```

This is more efficient as it caches the created `Color` structs.

---

[**Next: CLI Usage **](./08-cli-usage.md)
