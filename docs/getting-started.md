# Getting Started with PHPAnim

This guide will walk you through the process of setting up a new PHPAnim project and creating your first animation.

## Prerequisites

Before you begin, make sure you have the following installed:

- **PHP 8.0 or higher:** PHPAnim uses features specific to PHP 8, such as Fibers and the FFI extension.
- **FFI Extension:** The FFI (Foreign Function Interface) extension must be enabled in your `php.ini` file. You can check if it's enabled by running `php -m | grep FFI`.
- **Composer:** PHPAnim uses Composer for package management.
- **Raylib:** You'll need a compiled shared library of Raylib for your operating system.

## Setting Up Your Project

1.  **Create a new project directory:**

    ```bash
    mkdir my-phpanim-project
    cd my-phpanim-project
    ```

2.  **Initialize a new Composer project:**

    ```bash
    composer init
    ```

    Follow the prompts to create a `composer.json` file.

3.  **Install PHPAnim:**

    ```bash
    composer require aashan/phpanim
    ```

4.  **Download Raylib:**

    Download a pre-compiled version of Raylib for your operating system from the [Raylib releases page](https://github.com/raysan5/raylib/releases). Extract the archive and locate the shared library file (e.g., `libraylib.so`, `raylib.dll`, `libraylib.dylib`).

    For convenience, you can create a `lib` directory in your project root and place the Raylib library file there.

## Creating Your First Animation

Now that your project is set up, let's create a simple animation. Create a new file named `index.php` in your project root and add the following code:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;

// 1. Define the Raylib C functions you want to use
$cdef = <<<CDEF
typedef struct Color { unsigned char r, g, b, a; } Color;
void InitWindow(int, int, char *);
void CloseWindow();
bool WindowShouldClose();
void BeginDrawing();
void EndDrawing();
float GetFrameTime(void);
void SetTargetFPS(int fps);
int GetFPS(void);
void ClearBackground(Color color);
void DrawRectangle(int, int, int, int, Color);
CDEF;

// 2. Create a new Raylib instance
$rl = new Raylib(
    path: __DIR__ . '/lib/libraylib.dylib', // <-- Adjust the path to your Raylib library
    definition: $cdef
);

// 3. Create a state object for your animation
class State {
    public float $rectX = 100.0;
    public float $rectY = 100.0;
    public float $rectSize = 50.0;
}
$state = new State();

// 4. Initialize the window
$rl->InitWindow(800, 600, "My First PHPAnim Animation");
$rl->SetTargetFPS(60);

// 5. Create an animation scheduler
$scheduler = new Scheduler($rl, $state);
$scheduler
    ->tween('rectX', 100, 700, 2.0) // Animate 'rectX' from 100 to 700 over 2 seconds
    ->wait(1.0)                     // Wait for 1 second
    ->tween('rectY', 100, 500, 1.5) // Animate 'rectY' from 100 to 500 over 1.5 seconds
    ->tween('rectX', 700, 100, 2.0) // Animate 'rectX' back to 100
    ->wait(1.0)
    ->tween('rectY', 500, 100, 1.5) // Animate 'rectY' back to 100
    ->repeat()                      // Repeat the entire sequence
    ->start();                      // Start the animation

// 6. Main game loop
while (!$rl->WindowShouldClose()) {
    // Update the animation scheduler
    $scheduler->update();

    // Drawing
    $rl->BeginDrawing();

    $white = $rl->struct('Color', ['r' => 245, 'g' => 245, 'b' => 245, 'a' => 255]);
    $blue = $rl->struct('Color', ['r' => 0, 'g' => 121, 'b' => 241, 'a' => 255]);

    $rl->ClearBackground($white);

    $rl->DrawRectangle(
        (int)$state->rectX,
        (int)$state->rectY,
        (int)$state->rectSize,
        (int)$state->rectSize,
        $blue
    );

    $rl->DrawText("FPS: " . $rl->GetFPS(), 10, 10, 20, $blue);

    $rl->EndDrawing();
}

// 7. Close the window
$rl->CloseWindow();
```

### Running the Example

Run the following command in your terminal:

```bash
php index.php
```

You should see a window with a blue square animating back and forth.

## Next Steps

Now that you have a basic understanding of how to use PHPAnim, you can explore the more advanced features of the library:

- **Raylib API:** Learn how to call more Raylib functions and work with different data structures in the [Raylib API documentation](./raylib-api.md).
- **Scheduler API:** Discover the full power of the animation scheduler, including parallel animations and custom animation logic, in the [Scheduler API documentation](./scheduler-api.md).
- **Examples:** Check out the [examples](./examples.md) for more inspiration and to see how to build more complex animations and games.
