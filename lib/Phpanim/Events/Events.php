<?php

declare(strict_types=1);

namespace Phpanim\Events;

final class Events
{
    public const KeyPressed = 'keyboard.pressed';
    public const KeyReleased = 'keyboard.released';
    
    public const MouseClicked = 'mouse.clicked';
    public const MouseReleased = 'mouse.released';
    public const MouseMoved = 'mouse.moved';
    public const MouseScrolled = 'mouse.scrolled';
    
    public const GamepadButtonPressed = 'gamepad.button.pressed';
    public const GamepadAxisMoved = 'gamepad.axis.moved';
}