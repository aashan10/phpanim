# Raylib API

The `Raylib` class is the core of PHPAnim's integration with the Raylib library. It uses PHP's FFI (Foreign Function Interface) to load the Raylib shared library and provide a way to call its functions directly from PHP.

## Instantiating the `Raylib` Class

To get started, you need to create an instance of the `Raylib` class. The constructor takes two arguments:

- `path`: The path to the compiled Raylib shared library (`.so`, `.dll`, or `.dylib`).
- `definition`: A string containing the C declarations of the Raylib functions, structs, and enums you want to use.

```php
use Aashan\Phpanim\Raylib;

$cdef = <<<CDEF
// Structs
typedef struct Color { unsigned char r, g, b, a; } Color;
typedef struct Vector2 { float x; float y; } Vector2;

// Functions
void InitWindow(int width, int height, const char *title);
void DrawCircleV(Vector2 center, float radius, Color color);
// ... other functions
CDEF;

$rl = new Raylib(
    path: '/path/to/your/libraylib.so',
    definition: $cdef
);
```

### Finding C Definitions

You can find the C definitions for all Raylib functions, structs, and enums in the official `raylib.h` header file, which you can find in the `include` directory of the Raylib source code or a pre-compiled release.

## Calling Raylib Functions

Once you have a `Raylib` instance, you can call any of the functions you defined in the C definition string as if they were methods of the `Raylib` object:

```php
$rl->InitWindow(800, 600, "My Window");

$rl->BeginDrawing();
// ... drawing calls
$rl->EndDrawing();
```

The arguments you pass to these methods will be automatically converted to their corresponding C types.

## Working with C Structs

Raylib makes extensive use of structs, such as `Color`, `Vector2`, and `Rectangle`. The `Raylib` class provides a `struct` method to create and work with these C data structures in PHP.

The `struct` method takes two arguments:

- `name`: The name of the struct type (e.g., `"Color"`, `"Vector2"`).
- `params` (optional): An associative array of initial values for the struct's fields.

The method returns an `FFI\CData` object representing the C struct.

### Example: Creating a Color

```php
// Create a new Color struct with initial values
$red = $rl->struct('Color', [
    'r' => 255,
    'g' => 0,
    'b' => 0,
    'a' => 255
]);

// Use the color in a drawing function
$rl->ClearBackground($red);
```

### Example: Creating a Vector2

```php
$position = $rl->struct('Vector2', ['x' => 100.0, 'y' => 200.0]);

$rl->DrawCircleV($position, 50.0, $blue);
```

### Accessing and Modifying Struct Fields

You can access and modify the fields of a C struct just like you would with a PHP object:

```php
$player_position = $rl->struct('Vector2');

$player_position->x = 50;
$player_position->y = 100;

// Move the player to the right
$player_position->x += 10;
```

## Passing Structs by Value

When you pass a struct to a Raylib function, it is passed by value, meaning the function receives a copy of the struct. This is the standard behavior in C.

## Best Practices

- **Define only what you need:** To keep your C definition string clean and manageable, only include the declarations for the functions and structs you actually use in your application.
- **Cache struct instances:** Creating new `FFI\CData` objects can have a performance overhead. If you are using the same struct values repeatedly (e.g., colors), consider creating them once and reusing them throughout your game loop.
- **Consult the Raylib Cheat Sheet:** The [Raylib Cheat Sheet](https://www.raylib.com/cheatsheet/cheatsheet.html) is an excellent resource for finding the names and parameters of all available Raylib functions.
