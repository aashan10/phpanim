# 3. Core Architecture

PHPAnim is composed of several key components that work together to bring your animations to life. Understanding this architecture will help you use the library more effectively and extend it to suit your needs.

```
┌───────────────────┐
│   Your Project    │
│ (Scenes, Plugins) │
└───────────────────┘
        │
┌───────────────────┐
│      PHPAnim      │
├───────────────────┤
│  CLI Application  │
│  (Symfony Console)│
└───────────────────┘
        │
┌───────────────────┐      ┌───────────────────┐
│   PluginManager   │──────│   SceneManager    │
└───────────────────┘      └───────────────────┘
        │
┌───────────────────┐      ┌───────────────────┐
│     Scheduler     │      │   Visualizations  │
│     (Fibers)      │      │  (Grid, Curve...) │
└───────────────────┘      └───────────────────┘
        │
┌───────────────────┐
│   Raylib (FFI)    │
└───────────────────┘
        │
┌───────────────────┐
│ libraylib.so/.dll │
└───────────────────┘
```

## The Components

### 1. The CLI Application (`Phpanim`)

The entry point to the library is the `phpanim` command-line application, built using the **Symfony Console** component. This application is responsible for:

-   Parsing command-line arguments (like `--plugin-path` and scene names).
-   Loading and initializing the `Raylib` FFI layer.
-   Managing the main application loop.
-   Executing commands like `render` and `export`.

### 2. Raylib FFI Layer (`Raylib.php`)

This is the lowest-level part of the PHPAnim library. The `Raylib` class acts as a bridge to the native Raylib C library.

-   It uses **PHP's FFI** to load the shared library (`.so`, `.dylib`, `.dll`).
-   It allows you to call C functions (e.g., `InitWindow`, `DrawRectangle`) as if they were PHP methods (`$rl->InitWindow(...)`).
-   It provides a helper (`$rl->struct(...)`) to create and manage C data structures like `Color` and `Vector2`.

### 3. The Scheduler (`Scheduler.php`)

The Scheduler is the heart of PHPAnim's animation capabilities.

-   It is built on top of **PHP Fibers**, which allows it to perform non-blocking, sequential animations without complex callbacks or state machines.
-   You build an animation by chaining "blocks" of time-based actions (e.g., `tween`, `wait`, `custom`).
-   When `update()` is called in the main loop, the scheduler resumes its Fiber, calculates the new state for the current frame, and then yields control, waiting for the next frame.

### 4. Scenes (`SceneInterface`)

Scenes are where you define the logic for a specific animation or part of your application. A scene is any class that implements the `SceneInterface`.

-   `load(Raylib $rl)`: Called once when the scene starts. Use it to set up your initial state and animations.
-   `update(Raylib $rl)`: Called on every frame. This is where you put your drawing and update logic.
-   `unload(Raylib $rl)`: Called when the scene is finished.
-   `done(Raylib $rl)`: Must return `true` to signal that the scene has finished.

### 5. Plugins (`PluginInterface`)

Plugins are the mechanism for extending PHPAnim's functionality and integrating your scenes into the application.

-   A plugin is a PHP file that returns an object implementing `PluginInterface`.
-   PHPAnim includes a built-in `SceneManagerPlugin`, which is used to register your scenes with the main application. You add your scene instances to it, and the manager handles the lifecycle of the scene.

### 6. Visualizations

These are high-level, reusable components that encapsulate complex drawing logic. Instead of manually drawing a grid line by line, you can instantiate the `XYGrid` class.

-   Examples include `XYGrid` and `Curve`.
-   They are typically used inside a Scene and hold their own state.
-   They expose methods (`render()`) that can be called from your scene's `update()` method.

## How It All Ties Together: The `render` Command

When you run `./vendor/bin/phpanim render`:

1.  The **CLI Application** starts. It parses the command-line options.
2.  It instantiates the **Raylib FFI Layer** using the provided library and header paths.
3.  It finds and includes all `.php` files in the directory specified by `--plugin-path`.
4.  The **PluginManager** gathers all the valid `PluginInterface` objects returned by those files.
5.  The application calls the `initialize()` method on all loaded plugins. If one of them is a `SceneManagerPlugin`, the scene manager in turn calls the `load()` method on all of its registered scenes.
6.  It enters the main application loop:
    -   It calls the `update()` method on all loaded plugins.
    -   If a `SceneManagerPlugin` is running, it updates its active scene.
    -   Inside your scene's `update()`, you call `$scheduler->update()` and your drawing functions.
    -   The `Scheduler` updates the properties on your state objects.
    -   You use Raylib functions to draw shapes based on the new state.
7.  The loop continues until `WindowShouldClose()` returns `true`.
8.  Finally, the application calls the `unregister()` method on all plugins, which in turn calls the `unload()` method on your scenes, and then shuts down.

---

[**Next: Scenes and Plugins **](./04-scenes-and-plugins.md)
