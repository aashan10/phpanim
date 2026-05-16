<?php

use Phpanim\Animations\Builders\AnimationBuilder;
use Phpanim\Animations\AnimationQueue;
use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\KeyPressed;
use Phpanim\Input\Keyboard;
use Phpanim\Raylib\Raylib;
use Phpanim\Scenes\SceneInterface;

class SpringScene implements SceneInterface
{
    private object $target;

    private mixed $ballColor;

    private mixed $lineColor;

    private mixed $textColor;

    private mixed $dimColor;

    private bool $active = false;

    private bool $isDone = false;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private AnimationQueue $queue,
    ) {}

    public function register(): void
    {
        $this->ballColor = $this->raylib->GetColor(0x8B5CF6FF);
        $this->lineColor = $this->raylib->GetColor(0xD1D5DBFF);
        $this->textColor = $this->raylib->GetColor(0x111827FF);
        $this->dimColor = $this->raylib->GetColor(0x6B7280FF);

        $this->dispatcher->on(Events::KeyPressed, function (KeyPressed $event) {
            if (! $this->active) {
                return;
            }
            if ($event->keyName === Keyboard::SPACE) {
                $this->startAnimation();
            } elseif ($event->keyName === Keyboard::ENTER) {
                $this->isDone = true;
                $this->active = false;
            }
        });

        $this->active = true;
        $this->startAnimation();
    }

    public function reset(): void
    {
        $this->isDone = false;
        $this->active = true;
        $this->startAnimation();
    }

    public function isDone(): bool
    {
        return $this->isDone;
    }

    public function render(): void
    {
        // Guide lines
        $this->raylib->DrawLine(600, 180, 1000, 180, $this->lineColor);
        $this->raylib->DrawLine(600, 700, 1000, 700, $this->lineColor);
        $this->raylib->DrawText('start', 1010, 172, 16, $this->dimColor);
        $this->raylib->DrawText('target', 1010, 692, 16, $this->dimColor);

        // Ball
        $this->raylib->DrawCircle(800, (int) $this->target->y, 32, $this->ballColor);

        // Labels
        $this->raylib->DrawText('Spring', 60, 40, 48, $this->textColor);
        $this->raylib->DrawText('Stiffness: 120   Damping: 5   (underdamped)', 60, 100, 22, $this->dimColor);
        $this->raylib->DrawText('The spring overshoots and oscillates before settling.', 60, 132, 20, $this->dimColor);

        $this->raylib->DrawText('SPACE: replay   ENTER: next', 60, 858, 20, $this->dimColor);
        $this->raylib->DrawText('FPS: '.$this->raylib->GetFPS(), 1500, 858, 20, $this->dimColor);
    }

    private function startAnimation(): void
    {
        $this->target = (object) ['y' => 180.0];

        $ab = AnimationBuilder::make()->target($this->target);
        $ab->spring()
            ->property('y')
            ->from(180.0)
            ->to(700.0)
            ->stiffness(120.0)
            ->damping(5.0)
            ->then();
        $this->queue->addImmediateAnimation('physics.spring', $ab->build());
    }
}
