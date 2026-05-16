<?php

declare(strict_types=1);

namespace Phpanim\UI;

class ComponentManager
{
    private array $components = [];

    public function add(Component $component): static
    {
        $this->components[] = $component;
        $component->mount();

        return $this;
    }

    public function remove(Component $component): static
    {
        $component->destroy();
        $this->components = array_values(
            array_filter($this->components, fn ($c) => $c !== $component)
        );

        return $this;
    }

    public function render(): void
    {
        foreach ($this->components as $component) {
            $component->render();
        }
    }
}
