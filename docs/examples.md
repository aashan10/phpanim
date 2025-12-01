# PHPAnim Examples

This page contains a collection of examples to help you learn how to use PHPAnim. Each example is designed to demonstrate a specific feature of the library.

## 1. Basic Shape Animation

This example shows how to create a simple animation of a rectangle moving back and forth across the screen. It demonstrates the use of `tween` and `wait` to create a sequence of animations.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;

// Define the Raylib C functions
$cdef = <<<CDEF
typedef struct Color { unsigned char r, g, b, a; } Color;
void InitWindow(int, int, char *);
void CloseWindow();
bool WindowShouldClose();
void BeginDrawing();
void EndDrawing();
float GetFrameTime(void);
void SetTargetFPS(int fps);
int GetFPS(void);
void ClearBackground(Color color);
void DrawRectangle(int, int, int, int, Color);
CDEF;

// Create a Raylib instance
$rl = new Raylib(
    path: '/path/to/your/libraylib.so',
    definition: $cdef
);

// State object for the rectangle
class State {
    public float $posX = 0;
    public float $posY = 100;
    public float $size = 100;
}
$state = new State();

// Initialize the window
$rl->InitWindow(800, 600, "Basic Shape Animation");
$rl->SetTargetFPS(60);

// Create the animation scheduler
$scheduler = new Scheduler($rl, $state);
$scheduler
    ->tween('posX', 0, 700, 2)
    ->wait(1)
    ->tween('posX', 700, 0, 2)
    ->repeat()
    ->start();

// Main game loop
while (!$rl->WindowShouldClose()) {
    $rl->BeginDrawing();
    $scheduler->update();

    $white = $rl->struct('Color', ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255]);
    $rl->ClearBackground($white);

    $red = $rl->struct('Color', ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255]);
    $rl->DrawRectangle((int)$state->posX, (int)$state->posY, (int)$state->size, (int)$state->size, $red);

    $rl->DrawText("FPS:" . $rl->GetFPS(), 20, 20, 20, $red);
    $rl->EndDrawing();
}

$rl->CloseWindow();
```

## 2. Triangle Fan Animation

This example demonstrates how to use a `custom` animation to create a more complex effect. It creates a fan of triangles that rotate in a circle. It also shows how to use the `parallel` scheduler to run multiple animations at the same time.

```php
<?php

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;

require_once __DIR__ . '/../vendor/autoload.php';

// Raylib FFI Definition
$def = "
    typedef struct Color { unsigned char r, g, b, a; } Color;
    typedef struct Vector2 { float x; float y; } Vector2;
    void InitWindow(int, int, char *);
    void CloseWindow();
    bool WindowShouldClose();
    int GetScreenWidth(void); 
    int GetScreenHeight(void);
    void BeginDrawing();
    void EndDrawing();
    float GetFrameTime(void);
    void SetTargetFPS(int fps);
    int GetFPS(void);
    void ClearBackground(Color color);
    void DrawText(char *, int, int, int, Color);
    void DrawTriangle(Vector2, Vector2, Vector2, Color);
";
$rl = new Raylib(path: __DIR__ . '/raylib-5.5_macos/lib/libraylib.5.5.0.dylib', definition: $def);


$totalRotationDeg = 360.0;
$animationDuration = 2.0;
$triangleApexAngle = 15.0; 
$numTriangles = $totalRotationDeg / $triangleApexAngle;
$height = 200.0;

$halfApexAngleRad = deg2rad($triangleApexAngle / 2);
$base = 2 * $height * tan($halfApexAngleRad); 

$triangles = [];
$animations = [];

$rl->InitWindow(1600, 900, "Triangle Animation");
$rl->SetTargetFPS(60);

$centerX = $rl->GetScreenWidth() / 2;
$centerY = $rl->GetScreenHeight() / 2;

$white = $rl->struct('Color', ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255]);
$red = $rl->struct('Color', ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255]);

for ($i = 0; $i < $numTriangles; ++$i) {
    $triangle = new Triangle(
        x1: $centerX + $base / 2, y1: $centerY,
        x2: $centerX - $base / 2, y2: $centerY,
        x3: $centerX, y3: $centerY - $height
    );
    
    $initialAngleRad = deg2rad($i * $triangleApexAngle);
    $triangle->rotateInitial($centerX, $centerY, $initialAngleRad);
    
    $triangles[$i] = $triangle;

    $animation = new Scheduler($rl, $triangle);
    $animation->custom(function (Triangle $t, float $progress, float $dt) use($centerX, $centerY, $totalRotationDeg) {
        
        $angleRad = deg2rad($progress * $totalRotationDeg);
        $cosA = cos($angleRad);
        $sinA = sin($angleRad);

        // Point 1
        $t->x1 = $centerX + (($t->ox1 - $centerX) * $cosA) - (($t->oy1 - $centerY) * $sinA);
        $t->y1 = $centerY + (($t->ox1 - $centerX) * $sinA) + (($t->oy1 - $centerY) * $cosA);
        
        // Point 2
        $t->x2 = $centerX + (($t->ox2 - $centerX) * $cosA) - (($t->oy2 - $centerY) * $sinA);
        $t->y2 = $centerY + (($t->ox2 - $centerX) * $sinA) + (($t->oy2 - $centerY) * $cosA);

        // Point 3
        $t->x3 = $centerX + (($t->ox3 - $centerX) * $cosA) - (($t->oy3 - $centerY) * $sinA);
        $t->y3 = $centerY + (($t->ox3 - $centerX) * $sinA) + (($t->oy3 - $centerY) * $cosA);

    }, $animationDuration);

    $animations[$i] = $animation;
}

$scheduler = new Scheduler($rl, new stdClass())->parallel($animations);
$scheduler->start();

while(!$rl->WindowShouldClose()) {
    $rl->ClearBackground($white);
    $rl->BeginDrawing();
    
    $scheduler->update();

    foreach ($triangles as $triangle) {
        $v1 = $rl->struct('Vector2', ['x' => $triangle->x1, 'y' => $triangle->y1]);
        $v2 = $rl->struct('Vector2', ['x' => $triangle->x2, 'y' => $triangle->y2]);
        $v3 = $rl->struct('Vector2', ['x' => $triangle->x3, 'y' => $triangle->y3]);
        $rl->DrawTriangle($v2, $v1, $v3, $red); 
    }

    $rl->DrawText("FPS:" . $rl->GetFPS(), 20, 50, 20, $red);
    $rl->EndDrawing();
}

$rl->CloseWindow();

class Triangle 
{
    public float $x1, $y1, $x2, $y2, $x3, $y3;
    public float $ox1, $oy1, $ox2, $oy2, $ox3, $oy3;

    public function __construct(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3) {
        $this->x1 = $this->ox1 = $x1;
        $this->y1 = $this->oy1 = $y1;
        $this->x2 = $this->ox2 = $x2;
        $this->y2 = $this->oy2 = $y2;
        $this->x3 = $this->ox3 = $x3;
        $this->y3 = $this->oy3 = $y3;
    }

    public function rotateInitial(float $cx, float $cy, float $angleRad): void {
        $vertices = [&$this->ox1, &$this->oy1, &$this->ox2, &$this->oy2, &$this->ox3, &$this->oy3];
        $cosA = cos($angleRad);
        $sinA = sin($angleRad);

        for ($j = 0; $j < count($vertices); $j += 2) {
            $x_shifted = $vertices[$j] - $cx;
            $y_shifted = $vertices[$j + 1] - $cy;
            $vertices[$j] = $x_shifted * $cosA - $y_shifted * $sinA + $cx;
            $vertices[$j + 1] = $x_shifted * $sinA + $y_shifted * $cosA + $cy;
        }

        $this->x1 = $this->ox1; $this->y1 = $this->oy1;
        $this->x2 = $this->ox2; $this->y2 = $this->oy2;
        $this->x3 = $this->ox3; $this->y3 = $this->oy3;
    }
}
```
