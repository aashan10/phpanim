<?php

declare(strict_types=1);

namespace Phpanim\UI;

use Phpanim\Plugins\PluginInterface;

class ComponentManagerPlugin implements PluginInterface
{
    public function __construct(
        private ComponentManager $manager,
    ) {}

    public function register(): void {}

    public function render(): void
    {
        $this->manager->render();
    }
}
