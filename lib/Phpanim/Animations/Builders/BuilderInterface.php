<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders;

interface BuilderInterface
{
    public function then(): BuilderInterface;

    public function build(): callable;
}
