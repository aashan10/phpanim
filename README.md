# PHPAnim: A Creative Coding Framework for PHP

[![Latest Version](https://img.shields.io/packagist/v/aashan/phpanim.svg)](https://packagist.org/packages/aashan/phpanim)
[![License](https://img.shields.io/packagist/l/aashan/phpanim.svg)](https://github.com/aashan/phpanim/blob/main/LICENSE.txt)

**PHPAnim** is a creative coding framework for PHP that makes it possible to create 2D animations and interactive applications. It bridges the gap between the high-level, expressive power of PHP and the high-performance graphics capabilities of the [Raylib](https://www.raylib.com/) C library.

This is achieved using PHP's **FFI (Foreign Function Interface)**, which allows PHP code to call functions and use data structures from native, pre-compiled C libraries. What was once a simple library has evolved into a complete animation engine with a command-line interface, a plugin-based architecture, and a powerful animation scheduler.

## What is PHPAnim for?

-   **Creative Coding & Algorithmic Art**: Generate visuals from algorithms, create mathematical visualizations, or experiment with generative art, all within your favorite language.
-   **Prototyping Simple Games**: The simple, immediate-mode-like API is great for quickly prototyping 2D game mechanics.
-   **Educational Tools**: Build interactive tools and simulations for educational purposes.
-   **Pushing PHP to its Limits**: If you're curious about what PHP is capable of beyond web development, PHPAnim is a great way to explore its potential.

## Core Features

-   **Command-Line Interface**: A Symfony Console-based CLI for running and scaffolding your creations.
-   **Scene-Based Architecture**: Organize your application's logic into self-contained `Scenes`.
-   **Extensible via Plugins**: A simple yet powerful plugin system allows you to organize your code and extend the core functionality.
-   **Powerful Animation Scheduler**: A unique scheduler built on top of PHP **Fibers**. It allows you to write complex, non-blocking, and sequential animations with a clean, readable, and fluent API.
-   **High-Level Components**: PHPAnim includes built-in, high-level "Visualizations" like `XYGrid` and `Curve` that make it easy to create complex drawings with minimal code.
-   **Direct Raylib Access**: Provides a thin, object-oriented wrapper around the native Raylib library. You can call Raylib functions as if they were native PHP methods.

## Getting Started

### Prerequisites

1.  **PHP >= 8.0**: PHPAnim relies on features introduced in PHP 8, such as Fibers.
2.  **FFI Extension**: PHP's Foreign Function Interface extension must be enabled. Check by running `php -m | grep FFI`.
3.  **Composer**: The project uses Composer to manage PHP dependencies.
4.  **Raylib Shared Library**: You need a compiled Raylib shared library (`.so` on Linux, `.dylib` on macOS, `.dll` on Windows).

### Installation and Setup

1.  **Create a Project**:
    ```bash
    mkdir my-animation
    cd my-animation
    composer init
    ```

2.  **Install PHPAnim**:
    ```bash
    composer require aashan/phpanim
    ```

3.  **Get the Raylib Library**:
    Download the Raylib shared library from the [Raylib releases page](https://github.com/raysan5/raylib/releases). Create a `lib` folder in your project root and copy the library file into it (e.g., `lib/libraylib.dylib`).

## Basic Usage

PHPAnim is designed around **Scenes** and **Plugins**.

### 1. Create a Scene

A scene contains the logic for one part of your application. Create a file `MyFirstScene.php`:

```php
<?php

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scenes\SceneInterface;
use Aashan\Phpanim\Scheduler;

class MyFirstScene implements SceneInterface
{
    private Scheduler $scheduler;
    private object $state;

    public function __construct()
    {
        $this->state = new class {
            public float $rectX = 100.0;
        };
    }

    public function load(Raylib $rl): void
    {
        // Set up the animation sequence
        $this->scheduler = new Scheduler($rl, $this->state);
        $this->scheduler
            ->tween('rectX', 100, 700, 2.0)
            ->tween('rectX', 700, 100, 2.0)
            ->repeat()
            ->start();
    }

    public function update(Raylib $rl): void
    {
        $this->scheduler->update();

        $white = $rl->struct('Color', ['r' => 245, 'g' => 245, 'b' => 245, 'a' => 255]);
        $blue = $rl->struct('Color', ['r' => 0, 'g' => 121, 'b' => 241, 'a' => 255]);

        $rl->ClearBackground($white);
        $rl->DrawRectangle((int)$this->state->rectX, 100, 50, 50, $blue);
        $rl->DrawText("FPS: " . $rl->GetFPS(), 10, 10, 20, $blue);
    }

    public function unload(Raylib $rl): void {}

    public function done(Raylib $rl): bool
    {
        // Return true to finish the scene
        return false;
    }
}
```

### 2. Create a Plugin

Plugins register your scenes with the application. Create a `plugins` directory and add `main_plugin.php`:

```php
<?php
// plugins/main_plugin.php

require_once __DIR__ . '/../MyFirstScene.php';

use Aashan\Phpanim\Plugins\SceneManagerPlugin;

$sceneManager = new SceneManagerPlugin();
$sceneManager->addScene('my-scene', new MyFirstScene());

return $sceneManager;
```

### 3. Run the Animation

Use the `phpanim` command-line tool to render your scene.

```bash
./vendor/bin/phpanim render \
    --plugin-path=./plugins \
    --raylib-dll-path=./lib/libraylib.dylib
```

A window should appear, showing a blue square animating back and forth.

## Documentation

For more detailed information on the architecture, scheduler, visualizations, and CLI usage, please see the [**Full Documentation**](./docs/README.md).

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue.

## License

This project is licensed under the MIT License. See the [LICENSE](./LICENSE.txt) file for details.
