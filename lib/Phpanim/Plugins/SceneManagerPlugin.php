<?php

declare(strict_types=1);

namespace Phpanim\Plugins;

use Phpanim\Scenes\SceneInterface;
use Phpanim\Scenes\SceneManager;

class SceneManagerPlugin implements PluginInterface
{
    public function __construct(
        private SceneManager $sceneManager
    ) {}

    public function addScene(string $name, string|SceneInterface $scene): self
    {
        if (is_string($scene)) {
            $scene = app()->make($scene);
        }

        $this->sceneManager->add($name, $scene);

        return $this;
    }

    public function setSceneOrder(array $order): self
    {
        $this->sceneManager->setSceneOrder($order);

        return $this;
    }

    public function register(): void
    {
        $this->sceneManager->initialize();
    }

    public function render(): void
    {
        $this->sceneManager->render();
    }

    public function transitionTo(string $sceneName): bool
    {
        return $this->sceneManager->transitionTo($sceneName);
    }

    public function transitionToNext(): bool
    {
        return $this->sceneManager->transitionToNext();
    }

    public function getCurrentSceneName(): ?string
    {
        return $this->sceneManager->getCurrentSceneName();
    }
}
