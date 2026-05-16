<?php

use Phpanim\Animations\Builders\AnimationBuilder;
use Phpanim\Animations\AnimationQueue;
use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\KeyPressed;
use Phpanim\Input\Keyboard;
use Phpanim\Raylib\Raylib;
use Phpanim\Scenes\SceneInterface;

class PhysicsScene implements SceneInterface
{
    private object $target;

    private mixed $ballColor;

    private mixed $floorColor;

    private mixed $textColor;

    private mixed $dimColor;

    private bool $active = false;

    private bool $isDone = false;

    private const FLOOR_Y = 750;

    private const START_Y = 80.0;

    private const BALL_X = 800;

    private const RADIUS = 32;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private AnimationQueue $queue,
    ) {}

    public function register(): void
    {
        $this->ballColor = $this->raylib->GetColor(0x3B82F6FF);
        $this->floorColor = $this->raylib->GetColor(0x374151FF);
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
        // Floor
        $this->raylib->DrawLine(200, self::FLOOR_Y, 1400, self::FLOOR_Y, $this->floorColor);
        $this->raylib->DrawText('floor', 1410, self::FLOOR_Y - 8, 16, $this->dimColor);

        // Ball — sits on top of floor, so center is floor - radius
        $ballY = min((int) $this->target->y, self::FLOOR_Y - self::RADIUS);
        $this->raylib->DrawCircle(self::BALL_X, $ballY, self::RADIUS, $this->ballColor);

        // Labels
        $this->raylib->DrawText('Physics (Gravity + Bounce)', 60, 40, 48, $this->textColor);
        $this->raylib->DrawText('Gravity: 980 px/s²   Bounce: 0.65   (restitution)', 60, 100, 22, $this->dimColor);
        $this->raylib->DrawText('Each bounce loses 35% of velocity until the ball comes to rest.', 60, 132, 20, $this->dimColor);

        $this->raylib->DrawText('SPACE: drop again   ENTER: next', 60, 858, 20, $this->dimColor);
        $this->raylib->DrawText('FPS: '.$this->raylib->GetFPS(), 1500, 858, 20, $this->dimColor);
    }

    private function startAnimation(): void
    {
        // y tracks the bottom of the ball; floor contact is at FLOOR_Y
        $this->target = (object) ['y' => self::START_Y];

        $ab = AnimationBuilder::make()->target($this->target);
        $ab->physics()
            ->property('y')
            ->from(self::START_Y)
            ->gravity(980.0)
            ->bounce(0.65)
            ->floor((float) (self::FLOOR_Y - self::RADIUS))
            ->then();
        $this->queue->addImmediateAnimation('physics.gravity-bounce', $ab->build());
    }
}
