<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders\Traits;

trait ManipulatesProperty
{
    use HasTarget;

    protected ?string $property;

    public function property(string $property): static
    {
        $this->property = $property;

        return $this;
    }
}
