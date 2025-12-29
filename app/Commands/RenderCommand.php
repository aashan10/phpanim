<?php

declare(strict_types=1);

namespace App\Commands;

use Phpanim\Animations\Builders\AnimationBuilder;
use Phpanim\Animations\Executor;
use Phpanim\Raylib\Raylib;
use LaravelZero\Framework\Commands\Command;

class RenderCommand extends Command
{
    protected $signature = 'render
                            {scene? : The scene to render}
                            {--raylib-dll-path= : Path to the raylib dynamic library}
                            {--raylib-definition-path= : Path to the raylib header file}
                            {--plugin-path= : Path(s) to plugin files or directories}
                            {--width=1600 : Window width}
                            {--height=900 : Window height}
                            {--B|background-color=0xFFFFFFFF : Background color in HEX format}
                            {--title=Phpanim : Window title}';

    protected $description = 'Renders an animation scene';

    public function handle(Raylib $rl): int
    {
        $this->info('Starting Phpanim Renderer...');

        $rl->InitWindow(
            (int)$this->option('width'),
            (int)$this->option('height'),
            $this->option('title')
        );

        // Create animation target
        $target = (object) [
            'x' => 100.0,
            'y' => 100.0,
            'scale' => 1.0,
            'rotation' => 0.0,
        ];

        // Build animation sequence
        $animations = AnimationBuilder::make()
            ->target($target)
            ->group(function (AnimationBuilder $animation) {
                $animation->tween()
                    ->property('x')
                    ->from(100)
                    ->to(500)
                    ->duration(2.0)
                ->then()
                ->tween()
                    ->property('y')
                    ->from(100)
                    ->to(300)
                    ->duration(2.0)
                ->then()->build();
            })->parallel()
            ->then()
            ->wait(0.5)
            ->then()
            ->tween()
                ->property('x')
                ->from(500)
                ->to(100)
                ->duration(2.0)
            ->then()
            ->animations();

        $executor = Executor::withAnimations($animations);

        $executor->start();


        // Create colors
        $backgroundColor = $rl->GetColor(hexdec($this->option('background-color')));
        $rectColor = $rl->GetColor(0xFF4081FFFF); // Nice blue color

        while (!$rl->WindowShouldClose()) {

            $executor->update();
            $rl->BeginDrawing();
            $rl->ClearBackground($backgroundColor);

            // Draw animated rectangle
            $rl->DrawRectangle(
                (int)$target->x,
                (int)$target->y,
                (int)(50 * $target->scale),
                (int)(50 * $target->scale),
                $rectColor
            );

            // Draw debug info
            $rl->DrawText("FPS: " . $rl->GetFPS(), 10, 10, 20, $rectColor);
            $rl->DrawText(sprintf("Pos: (%.1f, %.1f)", $target->x, $target->y), 10, 70, 20, $rectColor);

            $rl->EndDrawing();
        }

        $rl->CloseWindow();

        return Command::SUCCESS;
    }
}
