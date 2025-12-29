<?php

declare(strict_types=1);

namespace App\Animations\Builders;

interface BuilderInterface
{
    public function then(): BuilderInterface;
    public function build(): callable;
}
