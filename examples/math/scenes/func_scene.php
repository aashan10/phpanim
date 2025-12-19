<?php
declare(strict_types=1);

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scenes\SceneInterface;
use Aashan\Phpanim\Scheduler;
use Aashan\Phpanim\Types\Vec2;
use Aashan\Phpanim\Visualizations\Curve;
use Aashan\Phpanim\Visualizations\XYGrid;

class FuncScene implements SceneInterface
{
    private Scheduler $scheduler;
    private bool $done = false;
    private XYGrid $graph;
    public Curve $curve;
    
    public function load(Raylib $rl): void
    {
        $screenWidth = $rl->GetScreenWidth();
        $screenHeight = $rl->GetScreenHeight();
        
        $this->graph = new XYGrid(
            origin: new Vec2($screenWidth / 2, $screenHeight / 2),
            spacing: 20.0,           // Grid line every 20 pixels
            unitSize: 20.0,          // 20 pixels = 1 unit
            labelInterval: 5,        // Show label every 5 grid lines (so every 100 pixels = 5 units)
            gridColor: 0xD0D0D0FF,   // Light gray for minor grid
            majorGridColor: 0xA0A0A0FF, // Darker gray for major grid
            axisColor: 0x000000FF,
            labelColor: 0x333333FF,
            fontSize: 10,
            showLabels: true,
            showTicks: true,
            labelPrecision: 0        // No decimal places for integers
        );
        
        $this->curve = new Curve(
            origin: new Vec2($screenWidth / 2, $screenHeight / 2),
            min: new Vec2(-35, -2),
            max: new Vec2(-35, 2),
            fn: function (float $x): float {
                return  sin($x);         // y = x²
            },
            color: 0xFF0000FF,          // Red
            thickness: 2.0,
            unitSize: 20.0,             // Match grid's unitSize
            segments: 100               // Smooth curve
        );
        
        $this->scheduler = new Scheduler($rl, $this);
        $this->scheduler
            ->tweenMulti(5, [
                'curve.max.x' => [-35, 35],
            ])
            ->custom(function ($target, float $progress, float $dt) use ($rl) {
                // Animation logic here
            }, 5)
            ->wait(1)
            ->custom(function ($target, float $progress, float $dt) use ($rl) {
                /* $this->done = true; */
            }, 2);
        $this->scheduler->start();
    }
    
    public function unload(Raylib $rl): void
    {
    }
    
    public function update(Raylib $rl): void
    {
        $this->scheduler->update();
        $this->graph->render($rl);
        $this->curve->render($rl);
    }
    
    public function done(Raylib $rl): bool
    {
        return $this->done;
    }
}
