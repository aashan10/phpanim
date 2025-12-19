# 8. CLI Usage

PHPAnim is controlled via the `phpanim` command-line interface, which is built with the Symfony Console component. You can run it from the `vendor/bin` directory of your project.

```bash
./vendor/bin/phpanim
```

This will show you the main help screen with a list of available commands and global options.

## Global Options

These options can be used with any command.

-   `--raylib-dll-path` (shortcut `-r`)
    -   **Description**: Specifies the path to your compiled Raylib shared library (`.so`, `.dylib`, or `.dll`).
    -   **Default**: PHPAnim attempts to guess the correct path based on your OS and the included `lib` directory.

-   `--raylib-definition-path` (shortcut `-d`)
    -   **Description**: Specifies the path to the C header file containing the Raylib function definitions.
    -   **Default**: The `raylib.h` file bundled with the PHPAnim library. You should rarely need to change this.

-   `--plugin-path` (shortcut `-p`)
    -   **Description**: Path to a directory containing your plugin files. PHPAnim will load all `.php` files from this directory.
    -   **Default**: The current working directory (`getcwd()`).

## Commands

### `render`

The `render` command runs your animation in a live window. It will load and run all scenes from all loaded plugins sequentially.

#### Usage

```bash
./vendor/bin/phpanim render [options]
```

#### Example

This command runs the application, loading all plugins from the `./plugins` directory and specifying the path to the Raylib library.

```bash
./vendor/bin/phpanim render \
    --plugin-path=./plugins \
    --raylib-dll-path=./lib/libraylib.dylib
```

### `export`

The `export` command is intended to record your animation and save it as a video file.

> **Note**: This feature is not yet fully implemented.

#### Usage

```bash
./vendor/bin/phpanim export [options]
```

---

[**&laquo; Previous: Using the Raylib FFI Layer**](./07-raylib-ffi.md)
