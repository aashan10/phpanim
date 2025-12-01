<?php

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;

require_once __DIR__ . '/../vendor/autoload.php';

// --- Raylib FFI Definition (Unchanged) ---
$def = "
    typedef struct Color {
        unsigned char r;
        unsigned char g;
        unsigned char b;
        unsigned char a;
    } Color;

    typedef struct Vector2 {
        float x;
        float y;
    } Vector2;

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
    void DrawRectangle(int, int, int, int, Color);
    void DrawTriangle(Vector2, Vector2, Vector2, Color);
";
$rl = new Raylib(path: __DIR__ . '/raylib-5.5_macos/lib/libraylib.5.5.0.dylib', definition: $def);


// --- CONSTANTS AND SETUP ---
$totalRotationDeg = 360.0;
$animationDuration = 2.0;

$triangleApexAngle = 15.0; 
$numTriangles = $totalRotationDeg / $triangleApexAngle;
$height = 200.0;

// --- CORRECTED BASE CALCULATION (Using radians) ---
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
    // 1. Define the base triangle (Initial position: Vertical, Apex pointing UP)
    $triangle = new Triangle(
        // V1: Base Right 
        x1: $centerX + $base / 2, 
        y1: $centerY,

        // V2: Base Left
        x2: $centerX - $base / 2, 
        y2: $centerY,

        // V3: Apex point 
        x3: $centerX, 
        y3: $centerY - $height,
    );
    
    // 2. Initial Rotation: Position the triangle correctly
    $initialAngleDeg = $i * $triangleApexAngle;
    $initialAngleRad = deg2rad($initialAngleDeg);

    $triangle->rotateInitial($centerX, $centerY, $initialAngleRad);
    
    $triangles[$i] = $triangle;

    // 3. Define the Animation
    $animation = new Scheduler($rl, $triangle);
    $animation->custom(function (Triangle $t, float $progress, float $dt) use($centerX, $centerY, $totalRotationDeg) {
        
        $angleDeg = $progress * $totalRotationDeg;
        $angleRad = deg2rad($angleDeg);

        $cosA = cos($angleRad);
        $sinA = sin($angleRad);

        // Point 1 (Base Right)
        $t->x1 = $centerX + (($t->ox1 - $centerX) * $cosA) - (($t->oy1 - $centerY) * $sinA);
        $t->y1 = $centerY + (($t->ox1 - $centerX) * $sinA) + (($t->oy1 - $centerY) * $cosA);
        
        // Point 2 (Base Left)
        $t->x2 = $centerX + (($t->ox2 - $centerX) * $cosA) - (($t->oy2 - $centerY) * $sinA);
        $t->y2 = $centerY + (($t->ox2 - $centerX) * $sinA) + (($t->oy2 - $centerY) * $cosA);

        // Point 3 (Apex)
        $t->x3 = $centerX + (($t->ox3 - $centerX) * $cosA) - (($t->oy3 - $centerY) * $sinA);
        $t->y3 = $centerY + (($t->ox3 - $centerX) * $sinA) + (($t->oy3 - $centerY) * $cosA);

    }, $animationDuration);

    $animations[$i] = $animation;
}

// Use a PARALLEL scheduler to run all animations simultaneously
$scheduler = new Scheduler($rl, new stdClass())->parallel($animations);
$scheduler->start();


// --- MAIN GAME LOOP ---
while(!$rl->WindowShouldClose()) {
    $rl->ClearBackground($white);
    $rl->BeginDrawing();
    
    $scheduler->update();

    foreach ($triangles as $triangle) {
        
        // Define Vector2 structs for drawing
        $v1 = $rl->struct('Vector2', ['x' => $triangle->x1, 'y' => $triangle->y1]);
        $v2 = $rl->struct('Vector2', ['x' => $triangle->x2, 'y' => $triangle->y2]);
        $v3 = $rl->struct('Vector2', ['x' => $triangle->x3, 'y' => $triangle->y3]);

        // FIX APPLIED HERE: Swap V1 and V2 in the DrawTriangle call to correct the winding.
        // The order V2, V1, V3 should render a visible triangle (Clockwise order in Y-down system).
        $rl->DrawTriangle($v2, $v1, $v3, $red); 
    }

    $rl->DrawText("FPS:" . $rl->GetFPS(), 20, 50, 20, $red);
    $rl->EndDrawing();
}


$rl->CloseWindow();


// --- TRIANGLE CLASS (Unchanged) ---
class Triangle 
{
    // Current (drawn) coordinates
    public float $x1, $y1;
    public float $x2, $y2;
    public float $x3, $y3;
    
    // Original (reference) coordinates for rotation
    public float $ox1, $oy1;
    public float $ox2, $oy2;
    public float $ox3, $oy3;

    public function __construct(
        float $x1, float $y1,
        float $x2, float $y2,
        float $x3, float $y3
    ) {
        $this->x1 = $this->ox1 = $x1;
        $this->y1 = $this->oy1 = $y1;

        $this->x2 = $this->ox2 = $x2;
        $this->y2 = $this->oy2 = $y2;

        $this->x3 = $this->ox3 = $x3;
        $this->y3 = $this->oy3 = $y3;
    }

    public function rotateInitial(float $cx, float $cy, float $angleRad): void
    {
        $vertices = [
            &$this->ox1, &$this->oy1, 
            &$this->ox2, &$this->oy2, 
            &$this->ox3, &$this->oy3
        ];

        $cosA = cos($angleRad);
        $sinA = sin($angleRad);

        for ($j = 0; $j < count($vertices); $j += 2) {
            $x = $vertices[$j];
            $y = $vertices[$j + 1];

            $x_shifted = $x - $cx;
            $y_shifted = $y - $cy;

            $x_rotated = $x_shifted * $cosA - $y_shifted * $sinA;
            $y_rotated = $x_shifted * $sinA + $y_shifted * $cosA;

            $vertices[$j] = $x_rotated + $cx;
            $vertices[$j + 1] = $y_rotated + $cy;
        }

        $this->x1 = $this->ox1; $this->y1 = $this->oy1;
        $this->x2 = $this->ox2; $this->y2 = $this->oy2;
        $this->x3 = $this->ox3; $this->y3 = $this->oy3;
    }
}
