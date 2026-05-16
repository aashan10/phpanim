<?php

declare(strict_types=1);

namespace Phpanim\Raylib;

use FFI;

class Raylib
{
    public function __construct(public readonly FFI $ffi) {}

    public function __call(string $name, array $params): mixed
    {
        return $this->ffi->{$name}(...$params);
    }

    public function __get(string $name): mixed
    {
        return $this->ffi->{$name};
    }

    public function struct(string $name, array $values = []): FFI\CData
    {
        $struct = $this->ffi->new($name);
        foreach ($values as $key => $value) {
            $struct->{$key} = $value;
        }

        return $struct;
    }
}
