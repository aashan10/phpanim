<?php

declare(strict_types=1);

namespace Aashan\Phpanim;

use FFI;
use FFI\CData;

/**
 *
 * @method void  InitWindow(int $width, int $height, string $title)
 * @method void  CloseWindow()
 *
 * @method bool  WindowShouldClose()
 * @method void  BeginDrawing()
 * @method void  EndDrawing()
 *
 * @method void  ClearBackground(CData $color)
 * @method void  DrawText(string $text, int $posX, int $posY, int $fontSize, CData $color)
 * @method void  DrawRectangle(int $posX, int $posY, int $width, int $height, CData $color)
 * @method void  DrawCircle(int $centerX, int $centerY, float $radius, CData $color)
 * @method void  DrawLine(int $startPosX, int $startPosY, int $endPosX, int $endPosY, CData $color)
 * @method void  DrawLineEx(CData $startPos, CData $endPos, float $thickness, CData $color)
 * @method void  DrawPixel(int $posX, int $posY, CData $color)
 * @method void  DrawTriangle(CData $v1, CData $v2, CData $v3, CData $color);
 *
 * @method int GetScreenWidth();
 * @method int GetScreenHeight();
 *
 * @method float GetFrameTime()
 * @method int   GetFPS()
 * @method void  SetTargetFPS(int $fps)
 * @method CData GetColor(int $hexValue)
 */
class Raylib
{
    public readonly FFI $ffi;

    public function __construct(string $path, string $definition)
    {
        $this->ffi = FFI::cdef(
            code: $definition,
            lib: $path,
        );
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->ffi->{$name}(...$arguments);
    }

    public function struct(string $name, array $params = []): CData
    {
        $type = $this->ffi->new($name);

        foreach ($params as $key => $value) {
            $type->{$key} = $value;
        }

        return $type;
    }
}
