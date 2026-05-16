<?php

use Phpanim\Plugins\PluginInterface;
use Phpanim\UI\ComponentManager;
use Phpanim\UI\Components\Button;
use Phpanim\UI\Components\FunctionPlot;
use Phpanim\UI\Components\XYGrid;

/** @var XYGrid $grid */
$grid = app()->make(XYGrid::class)
    ->bounds(200.0, 150.0, 1200.0, 600.0)
    ->pixelsPerUnit(10.0);

$grid->addPlot(
    app()->make(FunctionPlot::class)
        ->grid($grid)
        ->plot(fn (float $x, float $t): float => sin($x + $t))
        ->speed(1.5)
        ->color(0x38BDF8FF)
        ->label('sin(x + t)', 12)
);

$grid->addPlot(
    app()->make(FunctionPlot::class)
        ->grid($grid)
        ->plot(fn (float $x, float $t): float => cos($x + $t))
        ->speed(1.5)
        ->color(0xFBBF24FF)
        ->label('cos(x + t)', 36)
);

$grid->addPlot(
    app()->make(FunctionPlot::class)
        ->grid($grid)
        ->plot(fn (float $x, float $t): float => $x ** 3)
        ->speed(0.0)
        ->color(0xA78BFAFF)
        ->bezier()
        ->label('x³', 60)
);

// Reset button — positioned in the top-right corner inside the grid
$grid->addChild(
    app()->make(Button::class)
        ->bounds(
            $grid->getRect()->x + $grid->getRect()->width - 112.0,
            $grid->getRect()->y + 12.0,
            100.0,
            32.0,
        )
        ->label('Reset')
        ->onClick(fn () => $grid->reset())
);

app(ComponentManager::class)->add($grid);

return new class implements PluginInterface
{
    public function register(): void {

    }

    public function render(): void {}
};
