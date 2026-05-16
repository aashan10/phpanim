<?php

declare(strict_types=1);

namespace Phpanim\Input;

use Phpanim\Events\EventDispatcher;
use Phpanim\Events\KeyPressed;
use Phpanim\Events\KeyReleased;
use Phpanim\Raylib\Raylib;

class Keyboard
{
    public const SPACE = 'SPACE';

    public const APOSTROPHE = 'APOSTROPHE';

    public const COMMA = 'COMMA';

    public const MINUS = 'MINUS';

    public const PERIOD = 'PERIOD';

    public const SLASH = 'SLASH';

    public const NUM_0 = '0';

    public const NUM_1 = '1';

    public const NUM_2 = '2';

    public const NUM_3 = '3';

    public const NUM_4 = '4';

    public const NUM_5 = '5';

    public const NUM_6 = '6';

    public const NUM_7 = '7';

    public const NUM_8 = '8';

    public const NUM_9 = '9';

    public const SEMICOLON = 'SEMICOLON';

    public const EQUAL = 'EQUAL';

    public const A = 'A';

    public const B = 'B';

    public const C = 'C';

    public const D = 'D';

    public const E = 'E';

    public const F = 'F';

    public const G = 'G';

    public const H = 'H';

    public const I = 'I';

    public const J = 'J';

    public const K = 'K';

    public const L = 'L';

    public const M = 'M';

    public const N = 'N';

    public const O = 'O';

    public const P = 'P';

    public const Q = 'Q';

    public const R = 'R';

    public const S = 'S';

    public const T = 'T';

    public const U = 'U';

    public const V = 'V';

    public const W = 'W';

    public const X = 'X';

    public const Y = 'Y';

    public const Z = 'Z';

    public const LEFT_BRACKET = 'LEFT_BRACKET';

    public const BACKSLASH = 'BACKSLASH';

    public const RIGHT_BRACKET = 'RIGHT_BRACKET';

    public const GRAVE = 'GRAVE';

    public const ESCAPE = 'ESCAPE';

    public const ENTER = 'ENTER';

    public const TAB = 'TAB';

    public const BACKSPACE = 'BACKSPACE';

    public const INSERT = 'INSERT';

    public const DELETE = 'DELETE';

    public const RIGHT = 'RIGHT';

    public const LEFT = 'LEFT';

    public const DOWN = 'DOWN';

    public const UP = 'UP';

    public const PAGE_UP = 'PAGE_UP';

    public const PAGE_DOWN = 'PAGE_DOWN';

    public const HOME = 'HOME';

    public const END = 'END';

    public const CAPS_LOCK = 'CAPS_LOCK';

    public const SCROLL_LOCK = 'SCROLL_LOCK';

    public const NUM_LOCK = 'NUM_LOCK';

    public const PRINT_SCREEN = 'PRINT_SCREEN';

    public const PAUSE = 'PAUSE';

    public const F1 = 'F1';

    public const F2 = 'F2';

    public const F3 = 'F3';

    public const F4 = 'F4';

    public const F5 = 'F5';

    public const F6 = 'F6';

    public const F7 = 'F7';

    public const F8 = 'F8';

    public const F9 = 'F9';

    public const F10 = 'F10';

    public const F11 = 'F11';

    public const F12 = 'F12';

    public const KP_0 = 'KP_0';

    public const KP_1 = 'KP_1';

    public const KP_2 = 'KP_2';

    public const KP_3 = 'KP_3';

    public const KP_4 = 'KP_4';

    public const KP_5 = 'KP_5';

    public const KP_6 = 'KP_6';

    public const KP_7 = 'KP_7';

    public const KP_8 = 'KP_8';

    public const KP_9 = 'KP_9';

    public const KP_DECIMAL = 'KP_DECIMAL';

    public const KP_DIVIDE = 'KP_DIVIDE';

    public const KP_MULTIPLY = 'KP_MULTIPLY';

    public const KP_SUBTRACT = 'KP_SUBTRACT';

    public const KP_ADD = 'KP_ADD';

    public const KP_ENTER = 'KP_ENTER';

    public const KP_EQUAL = 'KP_EQUAL';

    public const LEFT_SHIFT = 'LEFT_SHIFT';

    public const LEFT_CONTROL = 'LEFT_CONTROL';

    public const LEFT_ALT = 'LEFT_ALT';

    public const LEFT_SUPER = 'LEFT_SUPER';

    public const RIGHT_SHIFT = 'RIGHT_SHIFT';

    public const RIGHT_CONTROL = 'RIGHT_CONTROL';

    public const RIGHT_ALT = 'RIGHT_ALT';

    public const RIGHT_SUPER = 'RIGHT_SUPER';

    public const MENU = 'MENU';

    private array $previousKeyState = [];

    private array $keyNames = [
        32 => self::SPACE,
        39 => self::APOSTROPHE,
        44 => self::COMMA,
        45 => self::MINUS,
        46 => self::PERIOD,
        47 => self::SLASH,
        48 => self::NUM_0, 49 => self::NUM_1, 50 => self::NUM_2, 51 => self::NUM_3, 52 => self::NUM_4,
        53 => self::NUM_5, 54 => self::NUM_6, 55 => self::NUM_7, 56 => self::NUM_8, 57 => self::NUM_9,
        59 => self::SEMICOLON,
        61 => self::EQUAL,
        65 => self::A, 66 => self::B, 67 => self::C, 68 => self::D, 69 => self::E, 70 => self::F,
        71 => self::G, 72 => self::H, 73 => self::I, 74 => self::J, 75 => self::K, 76 => self::L,
        77 => self::M, 78 => self::N, 79 => self::O, 80 => self::P, 81 => self::Q, 82 => self::R,
        83 => self::S, 84 => self::T, 85 => self::U, 86 => self::V, 87 => self::W, 88 => self::X,
        89 => self::Y, 90 => self::Z,
        91 => self::LEFT_BRACKET,
        92 => self::BACKSLASH,
        93 => self::RIGHT_BRACKET,
        96 => self::GRAVE,
        256 => self::ESCAPE,
        257 => self::ENTER,
        258 => self::TAB,
        259 => self::BACKSPACE,
        260 => self::INSERT,
        261 => self::DELETE,
        262 => self::RIGHT,
        263 => self::LEFT,
        264 => self::DOWN,
        265 => self::UP,
        266 => self::PAGE_UP,
        267 => self::PAGE_DOWN,
        268 => self::HOME,
        269 => self::END,
        280 => self::CAPS_LOCK,
        281 => self::SCROLL_LOCK,
        282 => self::NUM_LOCK,
        283 => self::PRINT_SCREEN,
        284 => self::PAUSE,
        290 => self::F1, 291 => self::F2, 292 => self::F3, 293 => self::F4,
        294 => self::F5, 295 => self::F6, 296 => self::F7, 297 => self::F8,
        298 => self::F9, 299 => self::F10, 300 => self::F11, 301 => self::F12,
        320 => self::KP_0, 321 => self::KP_1, 322 => self::KP_2, 323 => self::KP_3,
        324 => self::KP_4, 325 => self::KP_5, 326 => self::KP_6, 327 => self::KP_7,
        328 => self::KP_8, 329 => self::KP_9,
        330 => self::KP_DECIMAL,
        331 => self::KP_DIVIDE,
        332 => self::KP_MULTIPLY,
        333 => self::KP_SUBTRACT,
        334 => self::KP_ADD,
        335 => self::KP_ENTER,
        336 => self::KP_EQUAL,
        340 => self::LEFT_SHIFT,
        341 => self::LEFT_CONTROL,
        342 => self::LEFT_ALT,
        343 => self::LEFT_SUPER,
        344 => self::RIGHT_SHIFT,
        345 => self::RIGHT_CONTROL,
        346 => self::RIGHT_ALT,
        347 => self::RIGHT_SUPER,
        348 => self::MENU,
    ];

    public function __construct(
        private Raylib $raylib,
        private EventDispatcher $dispatcher
    ) {}

    public function poll(): void
    {
        $timestamp = microtime(true);

        foreach ($this->keyNames as $keycode => $keyName) {
            $isPressed = $this->raylib->IsKeyDown($keycode);
            $wasPressed = $this->previousKeyState[$keycode] ?? false;

            if ($isPressed && ! $wasPressed) {
                $this->dispatcher->dispatch('keyboard.pressed', new KeyPressed($keycode, $keyName, $timestamp));
            } elseif (! $isPressed && $wasPressed) {
                $this->dispatcher->dispatch('keyboard.released', new KeyReleased($keycode, $keyName, $timestamp));
            }

            $this->previousKeyState[$keycode] = $isPressed;
        }
    }

    public function isKeyDown(int $keycode): bool
    {
        return $this->raylib->IsKeyDown($keycode);
    }

    public function isKeyPressed(int $keycode): bool
    {
        return $this->raylib->IsKeyPressed($keycode);
    }

    public function getKeyName(int $keycode): string
    {
        return $this->keyNames[$keycode] ?? 'UNKNOWN';
    }
}
