# 2. Getting Started

This guide will walk you through setting up a PHPAnim project and running your first animation.

## Prerequisites

Before you start, ensure your environment meets these requirements:

1.  **PHP >= 8.0**: PHPAnim relies on features introduced in PHP 8, such as Fibers.
2.  **FFI Extension**: PHP's Foreign Function Interface extension must be enabled. To check, run `php -m | grep FFI`. If it's not listed, you'll need to enable it in your `php.ini` file.
3.  **Composer**: The project uses Composer to manage PHP dependencies.
4.  **Raylib Shared Library**: You need a compiled Raylib shared library (`.so` on Linux, `.dylib` on macOS, `.dll` on Windows).

## Step 1: Set Up the Project

First, create a directory for your project, navigate into it, and initialize a new Composer project.

```bash
mkdir my-animation
cd my-animation
composer init # Follow the interactive prompts
```

Next, install PHPAnim as a dependency:

```bash
composer require aashan/phpanim
```

## Step 2: Get the Raylib Library

PHPAnim ships with the Raylib C header file, but you need to provide the compiled dynamic library yourself.

1.  Go to the [Raylib releases page](https://github.com/raysan5/raylib/releases).
2.  Download the package for your operating system (e.g., `raylib-X.X_macos.zip`).
3.  Extract the archive. Inside the `lib` directory, you will find the shared library file (e.g., `libraylib.dylib`).

For convenience, create a `lib` folder in your project root and copy the Raylib library file into it.

Your project structure should now look like this:

```
my-animation/
├── lib/
│   └── libraylib.dylib  # Or .so, .dll
├── vendor/
└── composer.json
```

## Step 3: Create Your First Scene

PHPAnim's logic is organized into "Scenes". Create a file named `MyFirstScene.php` and add the following code:

```php
<?php

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scenes\SceneInterface;
use Aashan\Phpanim\Scheduler;

class MyFirstScene implements SceneInterface
{
    private Scheduler $scheduler;
    private bool $isDone = false;

    // A simple object to hold the state of our animation
    private object $state;

    public function __construct()
    {
        $this->state = new class {
            public float $rectX = 100.0;
            public float $rectY = 100.0;
        };
    }

    public function load(Raylib $rl): void
    {
        // Set up the animation sequence
        $this->scheduler = new Scheduler($rl, $this->state);
        $this->scheduler
            ->tween('rectX', 100, 700, 2.0) // Animate 'rectX' from 100 to 700 over 2 seconds
            ->wait(0.5)
            ->tween('rectY', 100, 500, 1.5)
            ->tween('rectX', 700, 100, 2.0)
            ->wait(0.5)
            ->tween('rectY', 500, 100, 1.5)
            ->repeat() // Loop the entire sequence
            ->start();
    }

    public function update(Raylib $rl): void
    {
        // Advance the animation on every frame
        $this->scheduler->update();

        // Define some colors
        $white = $rl->struct('Color', ['r' => 245, 'g' => 245, 'b' => 245, 'a' => 255]);
        $blue = $rl->struct('Color', ['r' => 0, 'g' => 121, 'b' => 241, 'a' => 255]);

        // Draw the background and the rectangle
        $rl->ClearBackground($white);
        $rl->DrawRectangle(
            (int)$this->state->rectX,
            (int)$this->state->rectY,
            50, // width
            50, // height
            $blue
        );

        $rl->DrawText("FPS: " . $rl->GetFPS(), 10, 10, 20, $blue);
    }

    public function unload(Raylib $rl): void
    {
        // Clean up resources here if needed
    }

    public function done(Raylib $rl): bool
    {
        return $this->isDone;
    }
}
```

## Step 4: Create a Plugin to Load the Scene

To make the application aware of your scene, you need to register it through a plugin.

First, create a directory for your plugins:

```bash
mkdir plugins
```

Now, create a file inside that directory, for example `plugins/main_plugin.php`:

```php
<?php

require_once __DIR__ . '/../MyFirstScene.php';

use Aashan\Phpanim\Plugins\SceneManagerPlugin;

// 1. Create a Scene Manager
$sceneManager = new SceneManagerPlugin();

// 2. Add an instance of your scene
// The name is just for your reference; it's not used by the runner
$sceneManager->addScene('my-scene', new MyFirstScene());

// 3. Return the configured plugin
return $sceneManager;
```

## Step 5: Run the Animation

You can now run your animation using the `phpanim` command-line tool. Use the `render` command and point the `--plugin-path` to the directory you just created.

```bash
./vendor/bin/phpanim render \
    --plugin-path=./plugins \
    --raylib-dll-path=./lib/libraylib.dylib
```

> **Note:** You may need to adjust the path to your `libraylib.dylib` (or `.so`/`.dll`) file.

A window should appear, showing a blue square animating around the screen. If you had added more scenes to the `SceneManagerPlugin`, they would run in sequence after the first one finishes.

---

[**Next: Core Architecture **](./03-core-architecture.md)
