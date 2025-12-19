# 1. Introduction

PHPAnim is a creative coding library for PHP that makes it possible to create 2D animations and interactive applications. It bridges the gap between the high-level, expressive power of PHP and the high-performance graphics capabilities of the [Raylib](https://www.raylib.com/) C library.

This is achieved using PHP's **FFI (Foreign Function Interface)**, which allows PHP code to call functions and use data structures from native, pre-compiled C libraries.

## What is PHPAnim for?

-   **Creative Coding & Algorithmic Art**: Generate visuals from algorithms, create mathematical visualizations, or experiment with generative art, all within your favorite language.
-   **Prototyping Simple Games**: The simple, immediate-mode-like API is great for quickly prototyping 2D game mechanics.
-   **Educational Tools**: Build interactive tools and simulations for educational purposes.
-   **Pushing PHP to its Limits**: If you're curious about what PHP is capable of beyond web development, PHPAnim is a great way to explore its potential.

## Core Features

-   **Direct Raylib Access**: Provides a thin, object-oriented wrapper around the native Raylib library. You can call Raylib functions as if they were native PHP methods.
-   **Powerful Animation Scheduler**: A unique scheduler built on top of PHP **Fibers**. It allows you to write complex, non-blocking, and sequential animations with a clean, readable, and fluent API.
-   **Extensible via Plugins**: A simple yet powerful plugin system allows you to organize your code and extend the core functionality of the library.
-   **High-Level Components**: PHPAnim includes built-in, high-level "Visualizations" like `XYGrid` and `Curve` that make it easy to create complex drawings with minimal code.
-   **Command-Line Interface**: Comes with a Symfony Console-based CLI for running and exporting your creations.

---

[**Next: Getting Started **](./02-getting-started.md)
