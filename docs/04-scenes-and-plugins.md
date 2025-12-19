# 4. Scenes and Plugins

In PHPAnim, your application's structure is built upon two key concepts: **Scenes** and **Plugins**.

-   **Scenes** contain the actual logic and graphics for a specific screen or animation (e.g., a menu, a level, a single visualization).
-   **Plugins** are the bridge that connects your scenes to the main application.
- **Plugins** run in parallel. The plugin manager finds all the plugins in a directory and runs them concurrently (multiple plugins can draw in a single frame) 
- **Scenes** are sequential. The scene manager runs scenes sequentially. No two scenes can draw at once (unless they are loaded by separate plugin manager instances which run in parallel)

## Scenes: The Building Blocks of Your Application

A Scene is a PHP class that implements the `Aashan\Phpanim\Scenes\SceneInterface`. This interface defines the lifecycle of a screen in your application.

### The `SceneInterface`

```php
interface SceneInterface
{
    /**
     * Called once when the scene is loaded.
     * Use this to initialize state, load assets, and set up schedulers.
     */
    public function load(Raylib $rl): void;

    /**
     * Called on every frame of the main application loop.
     * This is where you update state and draw everything.
     */
    public function update(Raylib $rl): void;

    /**
     * Called once when the scene is unloaded (e.g., when switching
     * to another scene or closing the application).
     */
    public function unload(Raylib $rl): void;

    /**
     * Signals to the application whether the scene has finished its work.
     * If this returns true, the application will unload the scene.
     */
    public function done(Raylib $rl): bool;
}
```

### How to Create a Scene

Here is a skeleton for a basic scene.

**`MyAwesomeScene.php`**
```php
<?php

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scenes\SceneInterface;
use Aashan\Phpanim\Scheduler;

class MyAwesomeScene implements SceneInterface
{
    private bool $isDone = false;
    private Scheduler $scheduler;
    private object $state;

    // Use the constructor to set up initial, non-graphics state
    public function __construct()
    {
        $this->state = new class {
            public float $circleRadius = 10.0;
        };
    }
    
    // `load` is for setting up animations and graphics-dependent state
    public function load(Raylib $rl): void
    {
        $this->scheduler = new Scheduler($rl, $this->state);
        $this->scheduler
            ->tween('circleRadius', 10, 100, 2)
            ->tween('circleRadius', 100, 10, 2)
            ->repeat()
            ->start();
    }
    
    // `update` is for drawing and logic that runs every frame
    public function update(Raylib $rl): void
    {
        // Always update your schedulers first
        $this->scheduler->update();
        
        $screenWidth = $rl->GetScreenWidth();
        $screenHeight = $rl->GetScreenHeight();
        $white = $rl->struct('Color', ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255]);
        $red = $rl->struct('Color', ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255]);

        $rl->ClearBackground($white);
        $rl->DrawCircle($screenWidth / 2, $screenHeight / 2, $this->state->circleRadius, $red);
    }
    
    public function unload(Raylib $rl): void
    {
        // Clean up any resources if necessary
    }
    
    public function done(Raylib $rl): bool
    {
        // Return true here to end the scene
        return $this->isDone;
    }
}
```

## Plugins: Extending the Application

A plugin is simply a `.php` file that returns an object that implements the `Aashan\Phpanim\Plugins\PluginInterface`.

The application loads plugins from a directory specified by the `--plugin-path` option. It will scan the directory for all `.php` files, include them, and register any that return a valid `PluginInterface` instance.

While you can create custom plugins, the most important built-in plugin is the **`SceneManagerPlugin`**. Its job is to manage your scenes and make them available to the main application.

### How to Create a Scene Manager Plugin

1.  **Create a directory for your plugins:** e.g., `mkdir plugins`.
2.  **Create a `scene_manager.php` file inside it:** The name can be anything.
3.  **Require your Scene class files:** Make sure the scene classes are loaded.
4.  **Instantiate `SceneManagerPlugin`:** Create a new instance of the scene manager.
5.  **Add your scenes:** Use the `addScene()` method to register instances of your scenes.
6.  **Return the manager:** The last line of the file must return the plugin instance.

**`plugins/scene_manager.php`**
```php
<?php

// Make sure your scene classes are available
require_once __DIR__ . '/../MyAwesomeScene.php';
require_once __DIR__ . '/../AnotherScene.php';

use Aashan\Phpanim\Plugins\SceneManagerPlugin;

// Create the manager
$sceneManager = new SceneManagerPlugin();

// Add scenes. They will run in the order they are added.
$sceneManager->addScene('awesome', new MyAwesomeScene());
$sceneManager->addScene('another', new AnotherScene());

// Return the configured manager
return $sceneManager;
```

### How Scenes Are Run

You cannot select a single scene to run from the command line. Instead, the `render` command executes all scenes from all loaded `SceneManagerPlugin`s.

The `SceneManagerPlugin` will run each scene you added to it **sequentially**. It will `load` and `update` the first scene until its `done()` method returns `true`. Then, it will move to the next scene in the list and repeat the process.

To run your application, point the `--plugin-path` to your new directory:

```bash
# This will run 'MyAwesomeScene', and then 'AnotherScene' right after it finishes.
./vendor/bin/phpanim render --plugin-path=./plugins
```

This structure allows you to chain multiple scenes together to create a larger application with different screens or levels.

### When to use Scene and when to use Plugin? 

At least one plugin is always required to bootstrap your animations. So it is essential. However, scenes are more for managing the "scenes". They are not required and you can still draw everything from a plugin. But when you are creating complex animations one sequentially, splitting the things to plugins make a lot of sense because you are basically encapsulating independent code into a separate module that handles just a single task.

So, general rule of thumb is start with plugins. If your codebase gradually becomes larger, it might make sense to split the animations into scenes.

---

[**Next: The Scheduler **](./05-scheduler.md)
