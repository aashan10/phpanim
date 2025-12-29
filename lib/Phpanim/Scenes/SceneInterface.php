<?php

declare(strict_types=1);

namespace Phpanim\Scenes;

interface SceneInterface
{
    public function register(): void;

    public function isDone(): bool;

    public function render(): void;
}
