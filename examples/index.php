<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Aashan\Phpanim\Raylib;
use Aashan\Phpanim\Scheduler;

$def = "
    typedef struct Color {
        unsigned char r;
        unsigned char g;
        unsigned char b;
        unsigned char a;
    } Color;

    void InitWindow(int, int, char *);
    void CloseWindow();
    bool WindowShouldClose();
    void BeginDrawing();
    void EndDrawing();

    float GetFrameTime(void);
    void SetTargetFPS(int fps);
    int GetFPS(void);

    void ClearBackground(Color color);

    void DrawText(char *, int, int, int, Color);
    void DrawRectangle(int, int, int, int, Color);
";

$rl = new Raylib(
    path      : __DIR__ .'/raylib-5.5_macos/lib/libraylib.5.5.0.dylib', 
    definition: $def
);

class State {
    public float $posX;
    public float $posY;
    public float $size;
}


$state = new State;
$state->posX = 0;
$state->posY = 0;
$state->size = 100;

$rl->InitWindow(800, 600, "PHPAnim");
$rl->SetTargetFPS(60);


$scheduler = new Scheduler($rl, $state);

$scheduler
    ->tween('posX', 0, 300, 2)
    ->wait(2.0)
    ->tween('posY', 0, 200, 2)
    ->wait(2)
    ->tween('posY', 200, 0, 2)
    ->wait(2)
    ->tween('posX', 300, 0, 2)
    ->repeat()
    ->start();


while(!$rl->WindowShouldClose()) {
    $rl->BeginDrawing();

    $scheduler->update();


    $white = $rl->struct('Color', ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255]);

    $rl->ClearBackground($white);

    $red = $rl->struct('Color', ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255]);
    $rl->DrawRectangle($state->posX, $state->posY, $state->size, $state->size, $red);


    $rl->DrawText("FPS:" . $rl->GetFPS(), 20, 550, 20, $red);
    $rl->EndDrawing();
}


$rl->CloseWindow();

