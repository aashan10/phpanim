<?php

use Phpanim\Animations\Builders\Traits\HasTarget;

// Concrete test double that exposes the trait publicly.
function makeAccessor(array|object $data): object
{
    return new class($data)
    {
        use HasTarget;

        public function __construct(array|object $target)
        {
            $this->target = $target;
        }
    };
}

// -------------------------------------------------------------------------
// read()
// -------------------------------------------------------------------------

test('reads a property from an object', function () {
    $obj = (object) ['x' => 42.0];
    $accessor = makeAccessor($obj);

    expect($accessor->read('x'))->toBe(42.0);
});

test('reads a key from an array', function () {
    $accessor = makeAccessor(['value' => 99]);

    expect($accessor->read('value'))->toBe(99);
});

test('reads a nested object property via dot notation', function () {
    $inner = (object) ['z' => 7.0];
    $outer = (object) ['pos' => $inner];
    $accessor = makeAccessor($outer);

    expect($accessor->read('pos.z'))->toBe(7.0);
});

test('reads a nested array key via dot notation', function () {
    $accessor = makeAccessor(['a' => ['b' => 'deep']]);

    expect($accessor->read('a.b'))->toBe('deep');
});

test('read throws InvalidArgumentException for a missing object property', function () {
    $accessor = makeAccessor((object) []);

    expect(fn () => $accessor->read('missing'))
        ->toThrow(\InvalidArgumentException::class);
});

test('read throws InvalidArgumentException for a missing array key', function () {
    $accessor = makeAccessor([]);

    expect(fn () => $accessor->read('missing'))
        ->toThrow(\InvalidArgumentException::class);
});

test('read throws when an intermediate segment is missing', function () {
    $accessor = makeAccessor((object) []);

    expect(fn () => $accessor->read('a.b.c'))
        ->toThrow(\InvalidArgumentException::class);
});

// -------------------------------------------------------------------------
// write()
// -------------------------------------------------------------------------

test('writes a property on an object and read returns the new value', function () {
    $obj = (object) ['x' => 0.0];
    $accessor = makeAccessor($obj);

    $accessor->write('x', 5.0);

    expect($obj->x)->toBe(5.0);
});

test('writes a key in an array and read returns the new value', function () {
    $accessor = makeAccessor(['count' => 0]);

    $accessor->write('count', 10);

    expect($accessor->read('count'))->toBe(10);
});

test('writes a nested object property via dot notation', function () {
    $inner = (object) ['y' => 0.0];
    $outer = (object) ['pos' => $inner];
    $accessor = makeAccessor($outer);

    $accessor->write('pos.y', 99.0);

    expect($inner->y)->toBe(99.0);
});

test('write throws InvalidArgumentException for a missing object property', function () {
    $accessor = makeAccessor((object) []);

    expect(fn () => $accessor->write('missing', 1.0))
        ->toThrow(\InvalidArgumentException::class);
});

test('write throws InvalidArgumentException for a missing array key', function () {
    $accessor = makeAccessor([]);

    expect(fn () => $accessor->write('missing', 1.0))
        ->toThrow(\InvalidArgumentException::class);
});

// -------------------------------------------------------------------------
// target()
// -------------------------------------------------------------------------

test('target() replaces the current target', function () {
    $first = (object) ['v' => 1.0];
    $second = (object) ['v' => 2.0];
    $accessor = makeAccessor($first);

    $accessor->target($second);

    expect($accessor->read('v'))->toBe(2.0);
});
