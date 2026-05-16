<?php

use Phpanim\Plugins\PluginInterface;
use Phpanim\Plugins\SceneManagerPlugin;

// Include all game files
require_once __DIR__.'/src/GameState.php';
require_once __DIR__.'/plugins/ScorePlugin.php';
require_once __DIR__.'/scenes/WelcomeScene.php';
require_once __DIR__.'/scenes/GameScene.php';
require_once __DIR__.'/scenes/GameOverScene.php';

class SnakeGamePlugin implements PluginInterface
{
    private GameState $gameState;

    private SceneManagerPlugin $sceneManager;

    private ScorePlugin $scorePlugin;

    public function __construct()
    {
        // Get scene manager from container
        $this->sceneManager = app()->get(SceneManagerPlugin::class);

        // Initialize the global game state
        $this->gameState = new GameState($this->sceneManager);

        // Register the game state as a singleton in the container
        app()->instance(GameState::class, $this->gameState);

        // Set up scenes
        $this->sceneManager
            ->addScene('welcome', WelcomeScene::class)
            ->addScene('game', GameScene::class)
            ->addScene('gameOver', GameOverScene::class);

        // Define scene order
        $this->sceneManager->setSceneOrder(['welcome', 'game', 'gameOver']);
        // Create score plugin
        $this->scorePlugin = app(ScorePlugin::class);
    }

    public function register(): void
    {
        // Register the score plugin
        $this->scorePlugin->register();
    }

    public function render(): void
    {
        // Render score overlay (plugin renders on all appropriate scenes)
        $this->scorePlugin->render();
    }
}

return new SnakeGamePlugin;
