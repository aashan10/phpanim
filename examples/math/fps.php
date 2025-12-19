<?php

use Aashan\Phpanim\Plugins\Plugin;
use Aashan\Phpanim\Raylib;

return new class extends Plugin {

    public function getName(): string
    {
        return 'FPS';
    }

    public function update(Raylib $raylib): void 
    {

        $raylib->DrawText(
            sprintf("FPS: %d", $raylib->GetFPS()),
            10, 10,
            20, 
            $raylib->GetColor(0xFF0000FF)
        );

    }
};
