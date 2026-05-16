<?php

declare(strict_types=1);

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Phpanim\Animations\AnimationQueue;
use Phpanim\Events\EventDispatcher;
use Phpanim\Input\InputManager;
use Phpanim\Plugins\PluginInterface;
use Phpanim\Plugins\PluginManager;
use Phpanim\Plugins\SceneManagerPlugin;
use Phpanim\Raylib\Raylib;
use Phpanim\UI\ComponentManagerPlugin;

class RenderCommand extends Command
{
    protected $signature = 'render
                            {scene? : The scene to render}
                            {--raylib-dll-path= : Path to the raylib dynamic library}
                            {--raylib-definition-path= : Path to the raylib header file}
                            {--p|plugin-path= : Path to plugin directory}
                            {--width=1600 : Window width}
                            {--height=900 : Window height}
                            {--B|background-color=0xFFFFFFFF : Background color in HEX format}
                            {--title=Phpanim : Window title}
                            {--msaa : Enable 4x multi-sample anti-aliasing}';

    protected $description = 'Renders an animation scene';

    public function handle(Raylib $rl): int
    {
        $this->info('Starting Phpanim Renderer...');

        // Get systems from container
        $dispatcher = app(EventDispatcher::class);
        $queue = app(AnimationQueue::class);
        $input = new InputManager($rl, $dispatcher);

        if ($this->option('msaa')) {
            $rl->SetConfigFlags(0x00000004); // FLAG_MSAA_4X_HINT
        }

        // Initialize window
        $rl->InitWindow(
            (int) $this->option('width'),
            (int) $this->option('height'),
            (string) $this->option('title')
        );

        // Load plugins
        $pluginManager = new PluginManager;
        $this->loadPlugins($pluginManager);

        // Initialize all plugins
        $pluginManager->initialize();

        // Create background color
        $backgroundColor = $rl->GetColor(hexdec($this->option('background-color')));

        // Main render loop
        while (! $rl->WindowShouldClose()) {
            // Begin drawing
            $rl->BeginDrawing();
            $rl->ClearBackground($backgroundColor);

            // Poll input events
            $input->poll();

            // Update animation systems
            $queue->update();

            // Render all plugins
            $pluginManager->render();

            $rl->EndDrawing();
        }

        $rl->CloseWindow();

        return Command::SUCCESS;
    }

    private function loadPlugins(PluginManager $pluginManager): void
    {
        // Load built-in plugins
        $scenePlugin = app()->make(SceneManagerPlugin::class);
        $pluginManager->add($scenePlugin);

        $componentPlugin = app()->make(ComponentManagerPlugin::class);
        $pluginManager->add($componentPlugin);

        $pluginPath = $this->option('plugin-path');
        if (! $pluginPath) {
            return;
        }

        if (! is_dir($pluginPath)) {
            $this->warn("Plugin directory does not exist: {$pluginPath}");

            return;
        }

        $this->info("Loading plugins from: {$pluginPath}");

        // Scan directory for PHP files
        $pluginFiles = glob($pluginPath.'/*.php');
        if (! $pluginFiles) {
            $this->warn('No PHP files found in plugin directory.');

            return;
        }

        $pluginCount = 0;
        foreach ($pluginFiles as $file) {
            try {
                $plugin = include $file;

                if ($plugin instanceof PluginInterface) {
                    $pluginManager->add($plugin);
                    $pluginCount++;
                    $this->info('Loaded plugin: '.basename($file));
                } else {
                    $this->warn('File does not return a PluginInterface: '.basename($file));
                }
            } catch (\Throwable $e) {
                $this->error('Failed to load plugin '.basename($file).': '.$e->getMessage());
            }
        }

        $this->info("Successfully loaded {$pluginCount} plugins.");
    }
}
