<?php
declare(strict_types=1);
namespace Phpanim\Plugins;

class PluginManager
{
    protected array $plugins = [];
    private bool $initialized = false;

    public function add(PluginInterface $plugin): self
    {
        if ($this->initialized) {
            throw new \RuntimeException('The plugin manager is already initialized.');
        }
        $this->plugins[] = $plugin;
        return $this;
    }

    public function initialize(): self
    {
        foreach ($this->plugins as $plugin) {
            $plugin->register();
        }
        $this->initialized = true;
        return $this;
    }

    public function render(): void
    {
        if (!$this->initialized) {
            throw new \RuntimeException('Plugin manager is not initialized yet. Call PluginManager::initialize() method first.');
        }

        // Render all plugins every frame
        foreach ($this->plugins as $plugin) {
            $plugin->render();
        }
    }
}
