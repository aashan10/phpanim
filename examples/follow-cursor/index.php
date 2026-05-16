<?php

use Phpanim\Animations\AnimationQueue;
use Phpanim\Animations\Builders\AnimationBuilder;
use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\KeyPressed;
use Phpanim\Events\MouseClicked;
use Phpanim\Events\MouseMoved;
use Phpanim\Input\Keyboard;
use Phpanim\Plugins\PluginInterface;
use Phpanim\Raylib\Raylib;

class FollowCursorPlugin implements PluginInterface
{
    private object $target;

    private mixed $rectColor;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private AnimationQueue $queue
    ) {}

    public function register(): void
    {
        // Create animation target
        $this->target = (object) [
            'x' => 100.0,
            'y' => 100.0,
            'scale' => 1.0,
            'rotation' => 0.0,
        ];

        // Set up event-driven animations
        AnimationBuilder::make()
            ->target($this->target)
            ->on(Events::KeyPressed, function (AnimationBuilder $animation, KeyPressed $event) {
                if ($event->keyName === Keyboard::SPACE) {
                    $animation->tween()
                        ->property('scale')
                        ->to(2.0)
                        ->duration(0.1)
                        ->then()
                        ->tween()
                        ->property('scale')
                        ->to(1.0)
                        ->duration(0.1)->then()->build();
                }
            })->then()
            ->on(Events::MouseMoved, function (AnimationBuilder $animation, MouseMoved $event) {
                $animation->group(function (AnimationBuilder $animation) use ($event) {
                    $animation->tween()->property('x')->to($event->x)->duration(0.5)->then()
                        ->tween()->property('y')->to($event->y)->duration(0.5)->then()->build();
                })->parallel()->then()->build();
            })->then()
            ->on(Events::MouseClicked, function (AnimationBuilder $animation, MouseClicked $event) {
                $animation->group(function (AnimationBuilder $animation) use ($event) {
                    $animation->tween()
                        ->property('x')
                        ->to($event->x - 25)
                        ->duration(0.5)
                        ->then()
                        ->tween()
                        ->property('y')
                        ->to($event->y - 25)
                        ->duration(0.5)->then()->build();
                })->parallel()->then()->build();
            });

        // Build and run base animation sequence through the shared queue
        $baseAnimation = AnimationBuilder::make()
            ->target($this->target)
            ->tween()
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
            ->then()
            ->build();
        $this->queue->addImmediateAnimation('follow-cursor.base', $baseAnimation);

        // Create rectangle color
        $this->rectColor = $this->raylib->GetColor(0xFF4081FFFF); // Nice blue color
    }

    public function render(): void
    {
        // Draw animated rectangle
        $this->raylib->DrawRectangle(
            (int) $this->target->x,
            (int) $this->target->y,
            (int) (50 * $this->target->scale),
            (int) (50 * $this->target->scale),
            $this->rectColor
        );

        // Draw debug info
        $this->raylib->DrawText('FPS: '.$this->raylib->GetFPS(), 10, 10, 20, $this->rectColor);
        $this->raylib->DrawText(sprintf('Pos: (%.1f, %.1f)', $this->target->x, $this->target->y), 10, 70, 20, $this->rectColor);
        $this->raylib->DrawText('Press SPACE to scale, move mouse to animate', 10, 40, 20, $this->rectColor);
    }
}

return app()->make(FollowCursorPlugin::class);
