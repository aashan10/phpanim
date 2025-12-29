<?php

declare(strict_types=1);

namespace Phpanim\Plugins;

interface PluginInterface
{
    public function register(): void;

    public function render(): void;
}
