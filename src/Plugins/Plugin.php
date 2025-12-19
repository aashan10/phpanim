<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Plugins;

use Aashan\Phpanim\Raylib;

abstract class Plugin implements PluginInterface
{
    public function register(Raylib $raylib): void
    {
        // Default implementation (can be overridden by subclasses)
    }

    public function unregister(Raylib $raylib): void
    {
        // Default implementation (can be overridden by subclasses)
    }

    public function update(Raylib $raylib): void
    {
    }
}
