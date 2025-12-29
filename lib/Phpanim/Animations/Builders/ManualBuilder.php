<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

use Phpanim\Animations\Builders\Traits\ResolvesProperties;

class ManualBuilder extends Builder
{
    use ResolvesProperties;

    private mixed $callback = null;

    public function __construct(
        private AnimationBuilder|GroupBuilder $parent
    )
    {}

    public function callback(callable $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    public function then(): BuilderInterface
    {
        return $this->parent->add($this);
    }

    public function build(): callable
    {
        if (!is_callable($this->callback)) {
            throw new \InvalidArgumentException("A callback must be provided for manual animations.");
        }

        return $this->callback;
    }
}