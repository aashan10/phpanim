<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Plugins;

use Aashan\Phpanim\Raylib;

interface PluginInterface
{
    public function getName(): string;

    public function register(Raylib $raylib): void;

    public function unregister(Raylib $raylib): void;

    public function update(Raylib $raylib): void;
}
