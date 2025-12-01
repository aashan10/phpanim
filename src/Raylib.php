<?php

declare(strict_types=1);

namespace Aashan\Phpanim;

use FFI;

/**
*
*
* @method void  InitWindow(int $width, int $height, string $title)
* @method void  CloseWindow()
*
* @method bool  WindowShouldClose()
* @method void  BeginDrawing()
* @method void  EndDrawing()
*
* @method void  ClearBackground(\FFI\CData $color)
* @method void  DrawText(string $text, int $posX, int $posY, int $fontSize, \FFI\CData $color)
* @method void  DrawRectangle(int $posX, int $posY, int $width, int $height, \FFI\CData $color)
* @method void  DrawCircle(int $centerX, int $centerY, float $radius, \FFI\CData $color)
* @method void  DrawLine(int $startPosX, int $startPosY, int $endPosX, int $endPosY, \FFI\CData $color)
* @method void  DrawPixel(int $posX, int $posY, \FFI\CData $color)
* @method void  DrawTriangle(\FFI\CData $v1, \FFI\CData $v2, \FFI\CData $v3, \FFI\CData $color);
* 
* @method int GetScreenWidth();
* @method int GetScreenHeight();
*
* @method float GetFrameTime()
* @method int   GetFPS()
* @method void  SetTargetFPS(int $fps)
*
*/
class Raylib 
{
    private readonly FFI $ffi;

    public function __construct(
        string $path,
        string $definition,
    ) {
        $this->ffi = FFI::cdef(code: $definition, lib: $path);
    }


    public function __call(string $name, array $arguments): mixed
    {
        return $this->ffi->{$name}(...$arguments);
    }

    public function struct(string $name, array $params = []): \FFI\CData
    {
        $type = $this->ffi->new($name);

        foreach ($params as $key => $value) {
            $type->{$key} = $value;
        }

        return $type;
    }
}
