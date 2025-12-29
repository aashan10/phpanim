<?php

declare(strict_types=1);

namespace Phpanim\Animations\Builders\Traits;

trait HasTarget
{
    protected array|object $target;

    public function read(string $path): mixed
    {
        $segments = explode('.', $path);
        $value = $this->target;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } elseif (is_object($value) && property_exists($value, $segment)) {
                $value = $value->$segment;
            } else {
                throw new \InvalidArgumentException("Property or key '$segment' does not exist.");
            }
        }

        return $value;
    }

    public function write(string $path, mixed $newValue): void
    {
        $segments = explode('.', $path);
        $lastSegment = array_pop($segments);
        $target = &$this->target;

        foreach ($segments as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = &$target[$segment];
            } elseif (is_object($target) && property_exists($target, $segment)) {
                $target = &$target->$segment;
            } else {
                throw new \InvalidArgumentException("Property or key '$segment' does not exist.");
            }
        }

        if (is_array($target) && array_key_exists($lastSegment, $target)) {
            $target[$lastSegment] = $newValue;
        } elseif (is_object($target) && property_exists($target, $lastSegment)) {
            $target->$lastSegment = $newValue;
        } else {
            throw new \InvalidArgumentException("Property or key '$lastSegment' does not exist.");
        }
    }

    public function target(array|object $target): static
    {
        $this->target = $target;

        return $this;
    }

}
