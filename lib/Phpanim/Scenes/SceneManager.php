<?php

declare(strict_types=1);

namespace Phpanim\Scenes;

class SceneManager
{
    protected array $scenes = [];

    private ?string $currentSceneName = null;

    private ?SceneInterface $currentScene = null;

    private array $sceneOrder = [];

    private bool $initialized = false;

    public function add(string $name, SceneInterface $scene): static
    {
        $this->scenes[$name] = $scene;
        $this->sceneOrder[] = $name;

        return $this;
    }

    public function remove(string $name): static
    {
        if (! isset($this->scenes[$name])) {
            return $this;
        }

        unset($this->scenes[$name]);
        $this->sceneOrder = array_values(array_diff($this->sceneOrder, [$name]));

        return $this;
    }

    public function get(string $name): ?SceneInterface
    {
        return $this->scenes[$name] ?? null;
    }

    public function all(): array
    {
        return $this->scenes;
    }

    public function initialize(): self
    {
        foreach ($this->scenes as $scene) {
            $scene->register();
        }

        // Start with the first scene
        if (! empty($this->sceneOrder)) {
            $this->currentSceneName = $this->sceneOrder[0];
            $this->currentScene = $this->scenes[$this->currentSceneName];
        }

        $this->initialized = true;

        return $this;
    }

    public function render(): void
    {
        if (! $this->initialized) {
            throw new \RuntimeException('Scene manager is not initialized yet. Call SceneManager::initialize() method first.');
        }

        if (! $this->currentScene) {
            return; // No scenes to render
        }

        // Render current scene
        $this->currentScene->render();

        // Check if scene is done, transition to next
        if ($this->currentScene->isDone()) {
            $this->transitionToNextScene();
        }
    }

    private function transitionToNextScene(): void
    {
        $currentIndex = array_search($this->currentSceneName, $this->sceneOrder);

        if ($currentIndex === false) {
            return;
        }

        $nextIndex = $currentIndex + 1;

        if (isset($this->sceneOrder[$nextIndex])) {
            $this->currentSceneName = $this->sceneOrder[$nextIndex];
            $this->currentScene = $this->scenes[$this->currentSceneName];

            // Reset the scene state when transitioning to it
            $this->currentScene->reset();
        } else {
            // No more scenes
            $this->currentScene = null;
            $this->currentSceneName = null;
        }
    }

    public function getCurrentScene(): ?SceneInterface
    {
        return $this->currentScene;
    }

    public function getCurrentSceneName(): ?string
    {
        return $this->currentSceneName;
    }

    public function hasActiveScene(): bool
    {
        return $this->currentScene !== null;
    }

    public function setSceneOrder(array $order): static
    {
        $this->sceneOrder = array_filter($order, fn ($name) => isset($this->scenes[$name]));

        return $this;
    }

    public function transitionTo(string $sceneName): bool
    {
        if (! isset($this->scenes[$sceneName])) {
            return false;
        }

        $this->currentSceneName = $sceneName;
        $this->currentScene = $this->scenes[$sceneName];

        // Reset the scene state when transitioning to it
        $this->currentScene->reset();

        return true;
    }

    public function transitionToNext(): bool
    {
        $this->transitionToNextScene();

        return $this->currentScene !== null;
    }
}
