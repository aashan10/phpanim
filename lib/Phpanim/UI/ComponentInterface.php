<?php

declare(strict_types=1);

namespace Phpanim\UI;

interface ComponentInterface
{
    public function mount(): void;

    public function render(): void;

    public function destroy(): void;
}
