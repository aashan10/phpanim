<?php

declare(strict_types=1);

namespace App\Animations\Builders\Traits;

trait ResolvesProperties
{
    use HasTarget, ManipulatesProperty;

    protected function resolve(mixed $value): mixed
    {
        if (is_callable($value)) {
            $current = $this->read($this->property);

            return $value($current);
        }

        return $value;
    }
}
