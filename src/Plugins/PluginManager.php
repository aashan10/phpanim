<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Plugins;

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;
use Symfony\Component\Finder\Finder;

final class PluginManager
{
    private array $plugins = [];

    public function __construct(
        private string $path,
    ) {
        $finder = new Finder();
        $finder->files()->in($this->path)->name('*.php')->depth('== 0');

        foreach ($finder as $file) {
            $plugin = include $file->getRealPath();

            if ($plugin instanceof PluginInterface) {
                $this->register($plugin);
            }
        }
    }

    public function register(PluginInterface $plugin): self
    {
        $this->plugins[$plugin->getName()] = $plugin;
        return $this;
    }

    public function initialize(Raylib $raylib): self
    {
        foreach ($this->plugins as $plugin) {
            $plugin->register($raylib);
        }
        return $this;
    }

    public function unregister(Raylib $raylib): self
    {
        foreach ($this->plugins as $plugin) {
            $plugin->unregister($raylib);
            unset($this->plugins[$plugin->getName()]);
        }
        return $this;
    }

    public function getUpdateScheduler(Raylib $raylib): Scheduler
    {
        $schedulers = array_map(
            array: $this->plugins,
            callback: static fn(PluginInterface $plugin) => new Scheduler(
                $raylib,
                new \stdClass(),
            )->manual(static function () use ($plugin, $raylib) {
                $plugin->update($raylib);
            }),
        );

        return new Scheduler($raylib, new \stdClass())->parallel($schedulers);
    }

    public function update(Raylib $raylib): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->update($raylib);
        }
    }
}
