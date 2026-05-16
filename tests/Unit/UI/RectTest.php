<?php

use Phpanim\UI\Rect;

test('constructor sets all properties', function () {
    $rect = new Rect(10.0, 20.0, 300.0, 150.0);

    expect($rect->x)->toBe(10.0)
        ->and($rect->y)->toBe(20.0)
        ->and($rect->width)->toBe(300.0)
        ->and($rect->height)->toBe(150.0);
});

test('contains returns true for a point clearly inside', function () {
    $rect = new Rect(10.0, 20.0, 100.0, 50.0);

    expect($rect->contains(60.0, 45.0))->toBeTrue();
});

test('contains returns false for a point clearly outside on all sides', function () {
    $rect = new Rect(10.0, 20.0, 100.0, 50.0);

    expect($rect->contains(5.0, 45.0))->toBeFalse()  // left
        ->and($rect->contains(115.0, 45.0))->toBeFalse() // right
        ->and($rect->contains(60.0, 15.0))->toBeFalse() // above
        ->and($rect->contains(60.0, 75.0))->toBeFalse(); // below
});

test('contains returns true on the top-left corner', function () {
    $rect = new Rect(10.0, 20.0, 100.0, 50.0);

    expect($rect->contains(10.0, 20.0))->toBeTrue();
});

test('contains returns true on the bottom-right corner', function () {
    $rect = new Rect(10.0, 20.0, 100.0, 50.0);

    expect($rect->contains(110.0, 70.0))->toBeTrue();
});

test('contains returns true on each edge', function () {
    $rect = new Rect(10.0, 20.0, 100.0, 50.0);

    expect($rect->contains(10.0, 45.0))->toBeTrue()  // left edge
        ->and($rect->contains(110.0, 45.0))->toBeTrue() // right edge
        ->and($rect->contains(60.0, 20.0))->toBeTrue()  // top edge
        ->and($rect->contains(60.0, 70.0))->toBeTrue(); // bottom edge
});

test('properties are mutable', function () {
    $rect = new Rect(0.0, 0.0, 100.0, 100.0);

    $rect->x = 50.0;
    $rect->y = 75.0;
    $rect->width = 200.0;
    $rect->height = 300.0;

    expect($rect->x)->toBe(50.0)
        ->and($rect->y)->toBe(75.0)
        ->and($rect->width)->toBe(200.0)
        ->and($rect->height)->toBe(300.0);
});

test('contains reflects mutations to the rect', function () {
    $rect = new Rect(100.0, 100.0, 100.0, 100.0);

    expect($rect->contains(50.0, 50.0))->toBeFalse();

    $rect->x = 0.0;
    $rect->y = 0.0;

    expect($rect->contains(50.0, 50.0))->toBeTrue();
});
