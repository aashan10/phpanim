# PHPAnim: 2D Animations with PHP and Raylib

PHPAnim is a PHP library that allows you to create 2D animations and games using the popular [Raylib](https://www.raylib.com/) library. It provides a simple and intuitive API for drawing shapes, handling input, and creating complex animations.

This library leverages PHP's FFI (Foreign Function Interface) to directly call functions from the Raylib C library, offering near-native performance while allowing you to write your game logic entirely in PHP.

## Features

- **Direct Raylib Access:** Use Raylib's powerful and extensive API directly from your PHP code.
- **Animation Scheduler:** A powerful scheduler that uses PHP Fibers to create complex, non-blocking animations with tweens, sequences, and parallel execution.
- **Easy to Use:** A simple and intuitive API that makes it easy to get started with 2D animation in PHP.
- **Cross-Platform:** Since Raylib is cross-platform, your PHPAnim applications can run on Windows, macOS, and Linux.

## Getting Started

### Prerequisites

- PHP 8.0 or higher with the FFI extension enabled.
- Composer for package management.
- A compiled Raylib shared library (`.so`, `.dll`, or `.dylib`).

### Installation

1.  **Install PHPAnim:**

    ```bash
    composer require aashan/phpanim
    ```

2.  **Download Raylib:**

    Download a pre-compiled version of Raylib for your operating system from the [Raylib releases page](https://github.com/raysan5/raylib/releases).

    Alternatively, you can compile it from source.

3.  **Place the Raylib library:**

    Place the compiled Raylib library file (e.g., `libraylib.so`, `raylib.dll`, `libraylib.dylib`) in a location accessible to your project.

### Basic Usage

Here's a simple example of how to create a window and draw a moving rectangle:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;

// Define the Raylib functions you want to use
$def = "
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
";

// Create a new Raylib instance
$rl = new Raylib(
    path: '/path/to/your/libraylib.so', // Or .dll or .dylib
    definition: $def
);

// A simple object to hold the state of our rectangle
class State {
    public float $posX = 0;
    public float $posY = 100;
    public float $size = 100;
}
$state = new State();

// Initialize the window
$rl->InitWindow(800, 600, "PHPAnim Basic Example");
$rl->SetTargetFPS(60);

// Create a scheduler to animate the rectangle
$scheduler = new Scheduler($rl, $state);
$scheduler
    ->tween('posX', 0, 700, 2)
    ->wait(1)
    ->tween('posX', 700, 0, 2)
    ->repeat()
    ->start();

// Main game loop
while (!$rl->WindowShouldClose()) {
    $rl->BeginDrawing();

    // Update the animation
    $scheduler->update();

    // Clear the background
    $white = $rl->struct('Color', ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255]);
    $rl->ClearBackground($white);

    // Draw the rectangle
    $red = $rl->struct('Color', ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255]);
    $rl->DrawRectangle((int)$state->posX, (int)$state->posY, (int)$state->size, (int)$state->size, $red);

    // Display FPS
    $rl->DrawText("FPS:" . $rl->GetFPS(), 20, 20, 20, $red);

    $rl->EndDrawing();
}

$rl->CloseWindow();
```
## Documentation

For more detailed information, please refer to the documentation in the `docs` directory:

- [Getting Started](./docs/getting-started.md)
- [Raylib API](./docs/raylib-api.md)
- [Scheduler API](./docs/scheduler-api.md)
- [Examples](./docs/examples.md)

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
