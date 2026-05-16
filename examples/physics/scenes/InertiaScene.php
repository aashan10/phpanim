<?php

use Phpanim\Animations\AnimationQueue;
use Phpanim\Animations\Builders\AnimationBuilder;
use Phpanim\Events\EventDispatcher;
use Phpanim\Events\Events;
use Phpanim\Events\KeyPressed;
use Phpanim\Input\Keyboard;
use Phpanim\Raylib\Raylib;
use Phpanim\Scenes\SceneInterface;

class InertiaScene implements SceneInterface
{
    private object $target;

    private mixed $ballColor;

    private mixed $trackColor;

    private mixed $textColor;

    private mixed $dimColor;

    private bool $active = false;

    private bool $isDone = false;

    private const START_X = 160.0;

    private const BALL_Y = 450;

    private const RADIUS = 32;

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher,
        private AnimationQueue $queue,
    ) {}

    public function register(): void
    {
        $this->ballColor = $this->raylib->GetColor(0x10B981FF);
        $this->trackColor = $this->raylib->GetColor(0xD1D5DBFF);
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
        // Track
        $this->raylib->DrawLine(120, self::BALL_Y + self::RADIUS + 4, 1480, self::BALL_Y + self::RADIUS + 4, $this->trackColor);

        // Ball
        $ballX = max(self::RADIUS, min((int) $this->target->x, 1600 - self::RADIUS));
        $this->raylib->DrawCircle($ballX, self::BALL_Y, self::RADIUS, $this->ballColor);

        // Start marker
        $this->raylib->DrawLine((int) self::START_X, self::BALL_Y - 60, (int) self::START_X, self::BALL_Y + 60, $this->trackColor);
        $this->raylib->DrawText('start', (int) self::START_X - 20, self::BALL_Y + 65, 16, $this->dimColor);

        // Labels
        $this->raylib->DrawText('Inertia (Fluid Drag)', 60, 40, 48, $this->textColor);
        $this->raylib->DrawText('Velocity: 500 px/s   Friction: 0.80   (exponential decay)', 60, 100, 22, $this->dimColor);
        $this->raylib->DrawText('Velocity decays proportionally — fast at first, asymptotically approaches zero.', 60, 132, 20, $this->dimColor);

        $this->raylib->DrawText('SPACE: relaunch   ENTER: next', 60, 858, 20, $this->dimColor);
        $this->raylib->DrawText('FPS: '.$this->raylib->GetFPS(), 1500, 858, 20, $this->dimColor);
    }

    private function startAnimation(): void
    {
        $this->target = (object) ['x' => self::START_X];

        $ab = AnimationBuilder::make()->target($this->target);
        $ab->inertia()
            ->property('x')
            ->from(self::START_X)
            ->velocity(500.0)
            ->friction(0.80)
            ->then();
        $this->queue->addImmediateAnimation('physics.inertia', $ab->build());
    }
}
