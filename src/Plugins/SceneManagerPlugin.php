<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Plugins;

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scenes\SceneInterface;
use Aashan\Phpanim\Scheduler;
use Fiber;
use stdClass;

final class SceneManagerPlugin extends Plugin
{
    private array $scenes = [];

    private bool $locked = false;

    private Scheduler $scheduler;

    public function register(Raylib $raylib): void
    {
        $this->locked = true;

        $this->scheduler = new Scheduler($raylib, new stdClass());

        array_map(
            array: $this->scenes,
            callback: fn(SceneInterface $scene) => $scene->load($raylib),
        );

        $this->scheduler->manual(function () use ($raylib) {
            foreach ($this->scenes as $scene) {
                while (!$scene->done($raylib)) {
                    $scene->update($raylib);
                    Fiber::suspend();
                }
            }
        });

        $this->scheduler->start();
    }

    public function unregister(Raylib $raylib): void
    {
        $this->locked = false;

        array_map(
            array: $this->scenes,
            callback: fn(SceneInterface $scene) => $scene->unload($raylib),
        );
    }

    public function update(Raylib $raylib): void
    {
        $this->scheduler->update();
    }

    public function addScene(string $name, SceneInterface $scene): self
    {
        if ($this->locked) {
            throw new \RuntimeException('Cannot add scenes after the SceneManagerPlugin has been registered.');
        }
        $this->scenes[$name] = $scene;
        return $this;
    }

    public function getName(): string
    {
        return 'Scene Manager';
    }
}
