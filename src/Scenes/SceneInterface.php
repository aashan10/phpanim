<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Scenes;

use Aashan\Phpanim\Raylib;

interface SceneInterface
{
    public function load(Raylib $rl): void;

    public function unload(Raylib $rl): void;

    public function update(Raylib $rl): void;

    public function done(Raylib $rl): bool;
}
