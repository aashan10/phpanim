// Vector2, 2 components
typedef struct Vector2 {
    float x;                // Vector x component
    float y;                // Vector y component
} Vector2;

// Vector3, 3 components
typedef struct Vector3 {
    float x;                // Vector x component
    float y;                // Vector y component
    float z;                // Vector z component
} Vector3;

// Vector4, 4 components
typedef struct Vector4 {
    float x;                // Vector x component
    float y;                // Vector y component
    float z;                // Vector z component
    float w;                // Vector w component
} Vector4;

// Matrix, 4x4 components, column major, OpenGL style, right-handed
typedef struct Matrix {
    float m0, m4, m8, m12;  // Matrix first row (4 components)
    float m1, m5, m9, m13;  // Matrix second row (4 components)
    float m2, m6, m10, m14; // Matrix third row (4 components)
    float m3, m7, m11, m15; // Matrix fourth row (4 components)
} Matrix;

// Color, 4 components, R8G8B8A8 (32bit)
typedef struct Color {
    unsigned char r;        // Color red value
    unsigned char g;        // Color green value
    unsigned char b;        // Color blue value
    unsigned char a;        // Color alpha value
} Color;

// Rectangle, 4 components
typedef struct Rectangle {
    float x;                // Rectangle top-left corner position x
    float y;                // Rectangle top-left corner position y
    float width;            // Rectangle width
    float height;           // Rectangle height
} Rectangle;

// Image, pixel data stored in CPU memory (RAM)
typedef struct Image {
    void *data;             // Image raw data
    int width;              // Image base width
    int height;             // Image base height
    int mipmaps;            // Mipmap levels, 1 by default
    int format;             // Data format (PixelFormat type)
} Image;

// Texture, tex data stored in GPU memory (VRAM)
typedef struct Texture {
    unsigned int id;        // OpenGL texture id
    int width;              // Texture base width
    int height;             // Texture base height
    int mipmaps;            // Mipmap levels, 1 by default
    int format;             // Data format (PixelFormat type)
} Texture;

// RenderTexture, fbo for texture rendering
typedef struct RenderTexture {
    unsigned int id;        // OpenGL framebuffer object id
    Texture texture;        // Color buffer attachment texture
    Texture depth;          // Depth buffer attachment texture
} RenderTexture;

// NPatchInfo, n-patch layout info
typedef struct NPatchInfo {
    Rectangle source;       // Texture source rectangle
    int left;               // Left border offset
    int top;                // Top border offset
    int right;              // Right border offset
    int bottom;             // Bottom border offset
    int layout;             // Layout of the n-patch: 3x3, 1x3 or 3x1
} NPatchInfo;

// GlyphInfo, font characters glyphs info
typedef struct GlyphInfo {
    int value;              // Character value (Unicode)
    int offsetX;            // Character offset X when drawing
    int offsetY;            // Character offset Y when drawing
    int advanceX;           // Character advance position X
    Image image;            // Character image data
} GlyphInfo;


// Camera, defines position/orientation in 3d space
typedef struct Camera3D {
    Vector3 position;       // Camera position
    Vector3 target;         // Camera target it looks-at
    Vector3 up;             // Camera up vector (rotation over its axis)
    float fovy;             // Camera field-of-view aperture in Y (degrees) in perspective, used as near plane height in world units in orthographic
    int projection;         // Camera projection: CAMERA_PERSPECTIVE or CAMERA_ORTHOGRAPHIC
} Camera3D;

// Camera2D, defines position/orientation in 2d space
typedef struct Camera2D {
    Vector2 offset;         // Camera offset (screen space offset from window origin)
    Vector2 target;         // Camera target (world space target point that is mapped to screen space offset)
    float rotation;         // Camera rotation in degrees (pivots around target)
    float zoom;             // Camera zoom (scaling around target), must not be set to 0, set to 1.0f for no scale
} Camera2D;

// Mesh, vertex data and vao/vbo
typedef struct Mesh {
    int vertexCount;        // Number of vertices stored in arrays
    int triangleCount;      // Number of triangles stored (indexed or not)

    // Vertex attributes data
    float *vertices;        // Vertex position (XYZ - 3 components per vertex) (shader-location = 0)
    float *texcoords;       // Vertex texture coordinates (UV - 2 components per vertex) (shader-location = 1)
    float *texcoords2;      // Vertex texture second coordinates (UV - 2 components per vertex) (shader-location = 5)
    float *normals;         // Vertex normals (XYZ - 3 components per vertex) (shader-location = 2)
    float *tangents;        // Vertex tangents (XYZW - 4 components per vertex) (shader-location = 4)
    unsigned char *colors;      // Vertex colors (RGBA - 4 components per vertex) (shader-location = 3)
    unsigned short *indices;    // Vertex indices (in case vertex data comes indexed)

    // Animation vertex data
    float *animVertices;    // Animated vertex positions (after bones transformations)
    float *animNormals;     // Animated normals (after bones transformations)
    unsigned char *boneIds; // Vertex bone ids, max 255 bone ids, up to 4 bones influence by vertex (skinning) (shader-location = 6)
    float *boneWeights;     // Vertex bone weight, up to 4 bones influence by vertex (skinning) (shader-location = 7)
    Matrix *boneMatrices;   // Bones animated transformation matrices
    int boneCount;          // Number of bones

    // OpenGL identifiers
    unsigned int vaoId;     // OpenGL Vertex Array Object id
    unsigned int *vboId;    // OpenGL Vertex Buffer Objects id (default vertex data)
} Mesh;

// Shader
typedef struct Shader {
    unsigned int id;        // Shader program id
    int *locs;              // Shader locations array (RL_MAX_SHADER_LOCATIONS)
} Shader;

// Bone, skeletal animation bone
typedef struct BoneInfo {
    char name[32];          // Bone name
    int parent;             // Bone parent
} BoneInfo;

// Ray, ray for raycasting
typedef struct Ray {
    Vector3 position;       // Ray position (origin)
    Vector3 direction;      // Ray direction (normalized)
} Ray;

// RayCollision, ray hit information
typedef struct RayCollision {
    bool hit;               // Did the ray hit something?
    float distance;         // Distance to the nearest hit
    Vector3 point;          // Point of the nearest hit
    Vector3 normal;         // Surface normal of hit
} RayCollision;

// BoundingBox
typedef struct BoundingBox {
    Vector3 min;            // Minimum vertex box-corner
    Vector3 max;            // Maximum vertex box-corner
} BoundingBox;

// Wave, audio wave data
typedef struct Wave {
    unsigned int frameCount;    // Total number of frames (considering channels)
    unsigned int sampleRate;    // Frequency (samples per second)
    unsigned int sampleSize;    // Bit depth (bits per sample): 8, 16, 32 (24 not supported)
    unsigned int channels;      // Number of channels (1-mono, 2-stereo, ...)
    void *data;                 // Buffer data pointer
} Wave;

// Opaque structs declaration
// NOTE: Actual structs are defined internally in raudio module
typedef struct rAudioBuffer rAudioBuffer;
typedef struct rAudioProcessor rAudioProcessor;

// AudioStream, custom audio stream
typedef struct AudioStream {
    rAudioBuffer *buffer;       // Pointer to internal data used by the audio system
    rAudioProcessor *processor; // Pointer to internal data processor, useful for audio effects

    unsigned int sampleRate;    // Frequency (samples per second)
    unsigned int sampleSize;    // Bit depth (bits per sample): 8, 16, 32 (24 not supported)
    unsigned int channels;      // Number of channels (1-mono, 2-stereo, ...)
} AudioStream;

// Sound
typedef struct Sound {
    AudioStream stream;         // Audio stream
    unsigned int frameCount;    // Total number of frames (considering channels)
} Sound;

// Music, audio stream, anything longer than ~10 seconds should be streamed
typedef struct Music {
    AudioStream stream;         // Audio stream
    unsigned int frameCount;    // Total number of frames (considering channels)
    bool looping;               // Music looping enable

    int ctxType;                // Type of music context (audio filetype)
    void *ctxData;              // Audio context data, depends on type
} Music;

// VrDeviceInfo, Head-Mounted-Display device parameters
typedef struct VrDeviceInfo {
    int hResolution;                // Horizontal resolution in pixels
    int vResolution;                // Vertical resolution in pixels
    float hScreenSize;              // Horizontal size in meters
    float vScreenSize;              // Vertical size in meters
    float eyeToScreenDistance;      // Distance between eye and display in meters
    float lensSeparationDistance;   // Lens separation distance in meters
    float interpupillaryDistance;   // IPD (distance between pupils) in meters
    float lensDistortionValues[4];  // Lens distortion constant parameters
    float chromaAbCorrection[4];    // Chromatic aberration correction parameters
} VrDeviceInfo;

// VrStereoConfig, VR stereo rendering configuration for simulator
typedef struct VrStereoConfig {
    Matrix projection[2];           // VR projection matrices (per eye)
    Matrix viewOffset[2];           // VR view offset matrices (per eye)
    float leftLensCenter[2];        // VR left lens center
    float rightLensCenter[2];       // VR right lens center
    float leftScreenCenter[2];      // VR left screen center
    float rightScreenCenter[2];     // VR right screen center
    float scale[2];                 // VR distortion scale
    float scaleIn[2];               // VR distortion scale in
} VrStereoConfig;

// File path list
typedef struct FilePathList {
    unsigned int capacity;          // Filepaths max entries
    unsigned int count;             // Filepaths entries count
    char **paths;                   // Filepaths entries
} FilePathList;

// Automation event
typedef struct AutomationEvent {
    unsigned int frame;             // Event frame
    unsigned int type;              // Event type (AutomationEventType)
    int params[4];                  // Event parameters (if required)
} AutomationEvent;

// module: core
// Window-related functions
void InitWindow(int width, int height, const char *title);  // Initialize window and OpenGL context
void CloseWindow(void);                                     // Close window and unload OpenGL context
bool WindowShouldClose(void);                               // Check if application should close (KEY_ESCAPE pressed or windows close icon clicked)
bool IsWindowReady(void);                                   // Check if window has been initialized successfully
bool IsWindowFullscreen(void);                              // Check if window is currently fullscreen
bool IsWindowHidden(void);                                  // Check if window is currently hidden
bool IsWindowMinimized(void);                               // Check if window is currently minimized
bool IsWindowMaximized(void);                               // Check if window is currently maximized
bool IsWindowFocused(void);                                 // Check if window is currently focused
bool IsWindowResized(void);                                 // Check if window has been resized last frame
bool IsWindowState(unsigned int flag);                      // Check if one specific window flag is enabled
void SetWindowState(unsigned int flags);                    // Set window configuration state using flags
void ClearWindowState(unsigned int flags);                  // Clear window configuration state flags
void ToggleFullscreen(void);                                // Toggle window state: fullscreen/windowed, resizes monitor to match window resolution
void ToggleBorderlessWindowed(void);                        // Toggle window state: borderless windowed, resizes window to match monitor resolution
void MaximizeWindow(void);                                  // Set window state: maximized, if resizable
void MinimizeWindow(void);                                  // Set window state: minimized, if resizable
void RestoreWindow(void);                                   // Set window state: not minimized/maximized
void SetWindowIcon(Image image);                            // Set icon for window (single image, RGBA 32bit)
void SetWindowIcons(Image *images, int count);              // Set icon for window (multiple images, RGBA 32bit)
void SetWindowTitle(const char *title);                     // Set title for window
void SetWindowPosition(int x, int y);                       // Set window position on screen
void SetWindowMonitor(int monitor);                         // Set monitor for the current window
void SetWindowMinSize(int width, int height);               // Set window minimum dimensions (for FLAG_WINDOW_RESIZABLE)
void SetWindowMaxSize(int width, int height);               // Set window maximum dimensions (for FLAG_WINDOW_RESIZABLE)
void SetWindowSize(int width, int height);                  // Set window dimensions
void SetWindowOpacity(float opacity);                       // Set window opacity [0.0f..1.0f]
void SetWindowFocused(void);                                // Set window focused
void *GetWindowHandle(void);                                // Get native window handle
int GetScreenWidth(void);                                   // Get current screen width
int GetScreenHeight(void);                                  // Get current screen height
int GetRenderWidth(void);                                   // Get current render width (it considers HiDPI)
int GetRenderHeight(void);                                  // Get current render height (it considers HiDPI)
int GetMonitorCount(void);                                  // Get number of connected monitors
int GetCurrentMonitor(void);                                // Get current monitor where window is placed
Vector2 GetMonitorPosition(int monitor);                    // Get specified monitor position
int GetMonitorWidth(int monitor);                           // Get specified monitor width (current video mode used by monitor)
int GetMonitorHeight(int monitor);                          // Get specified monitor height (current video mode used by monitor)
int GetMonitorPhysicalWidth(int monitor);                   // Get specified monitor physical width in millimetres
int GetMonitorPhysicalHeight(int monitor);                  // Get specified monitor physical height in millimetres
int GetMonitorRefreshRate(int monitor);                     // Get specified monitor refresh rate
Vector2 GetWindowPosition(void);                            // Get window position XY on monitor
Vector2 GetWindowScaleDPI(void);                            // Get window scale DPI factor
const char *GetMonitorName(int monitor);                    // Get the human-readable, UTF-8 encoded name of the specified monitor
void SetClipboardText(const char *text);                    // Set clipboard text content
const char *GetClipboardText(void);                         // Get clipboard text content
Image GetClipboardImage(void);                              // Get clipboard image
void EnableEventWaiting(void);                              // Enable waiting for events on EndDrawing(), no automatic event polling
void DisableEventWaiting(void);                             // Disable waiting for events on EndDrawing(), automatic events polling

// Cursor-related functions
void ShowCursor(void);                                      // Shows cursor
void HideCursor(void);                                      // Hides cursor
bool IsCursorHidden(void);                                  // Check if cursor is not visible
void EnableCursor(void);                                    // Enables cursor (unlock cursor)
void DisableCursor(void);                                   // Disables cursor (lock cursor)
bool IsCursorOnScreen(void);                                // Check if cursor is on the screen

// Drawing-related functions
void ClearBackground(Color color);                          // Set background color (framebuffer clear color)
void BeginDrawing(void);                                    // Setup canvas (framebuffer) to start drawing
void EndDrawing(void);                                      // End canvas drawing and swap buffers (double buffering)
void BeginMode2D(Camera2D camera);                          // Begin 2D mode with custom camera (2D)
void EndMode2D(void);                                       // Ends 2D mode with custom camera
void BeginMode3D(Camera3D camera);                          // Begin 3D mode with custom camera (3D)
void EndMode3D(void);                                       // Ends 3D mode and returns to default 2D orthographic mode
void EndTextureMode(void);                                  // Ends drawing to render texture
void BeginShaderMode(Shader shader);                        // Begin custom shader drawing
void EndShaderMode(void);                                   // End custom shader drawing (use default shader)
void BeginBlendMode(int mode);                              // Begin blending mode (alpha, additive, multiplied, subtract, custom)
void EndBlendMode(void);                                    // End blending mode (reset to default: alpha blending)
void BeginScissorMode(int x, int y, int width, int height); // Begin scissor mode (define screen area for following drawing)
void EndScissorMode(void);                                  // End scissor mode
void BeginVrStereoMode(VrStereoConfig config);              // Begin stereo rendering (requires VR simulator)
void EndVrStereoMode(void);                                 // End stereo rendering (requires VR simulator)

// utility functions 
int MeasureText(const char *text, int fontSize);
Color GetColor(unsigned int hexValue);               // Get Color structure from hexadecimal value

// VR stereo config functions for VR simulator
VrStereoConfig LoadVrStereoConfig(VrDeviceInfo device);     // Load VR stereo config for VR simulator device parameters
void UnloadVrStereoConfig(VrStereoConfig config);           // Unload VR stereo config

// Shader management functions
// NOTE: Shader functionality is not available on OpenGL 1.1
Shader LoadShader(const char *vsFileName, const char *fsFileName);   // Load shader from files and bind default locations
Shader LoadShaderFromMemory(const char *vsCode, const char *fsCode); // Load shader from code strings and bind default locations
bool IsShaderValid(Shader shader);                                   // Check if a shader is valid (loaded on GPU)
int GetShaderLocation(Shader shader, const char *uniformName);       // Get shader uniform location
int GetShaderLocationAttrib(Shader shader, const char *attribName);  // Get shader attribute location
void SetShaderValue(Shader shader, int locIndex, const void *value, int uniformType);               // Set shader uniform value
void SetShaderValueV(Shader shader, int locIndex, const void *value, int uniformType, int count);   // Set shader uniform value vector
void SetShaderValueMatrix(Shader shader, int locIndex, Matrix mat);         // Set shader uniform value (matrix 4x4)
void UnloadShader(Shader shader);                                    // Unload shader from GPU memory (VRAM)

// Timing-related functions
void SetTargetFPS(int fps);                                 // Set target FPS (maximum)
float GetFrameTime(void);                                   // Get time in seconds for last frame drawn (delta time)
double GetTime(void);                                       // Get elapsed time in seconds since InitWindow()
int GetFPS(void);                                           // Get current FPS

// Custom frame control functions
// NOTE: Those functions are intended for advanced users that want full control over the frame processing
// By default EndDrawing() does this job: draws everything + SwapScreenBuffer() + manage frame timing + PollInputEvents()
// To avoid that behaviour and control frame processes manually, enable in config.h: SUPPORT_CUSTOM_FRAME_CONTROL
void SwapScreenBuffer(void);                                // Swap back buffer with front buffer (screen drawing)
void PollInputEvents(void);                                 // Register all input events
void WaitTime(double seconds);                              // Wait for some time (halt program execution)

// Random values generation functions
void SetRandomSeed(unsigned int seed);                      // Set the seed for the random number generator
int GetRandomValue(int min, int max);                       // Get a random value between min and max (both included)
int *LoadRandomSequence(unsigned int count, int min, int max); // Load random values sequence, no values repeated
void UnloadRandomSequence(int *sequence);                   // Unload random values sequence

// Misc. functions
void TakeScreenshot(const char *fileName);                  // Takes a screenshot of current screen (filename extension defines format)
void SetConfigFlags(unsigned int flags);                    // Setup init configuration flags (view FLAGS)
void OpenURL(const char *url);                              // Open URL with default system browser (if available)

// NOTE: Following functions implemented in module [utils]
//------------------------------------------------------------------
void TraceLog(int logLevel, const char *text, ...);         // Show trace log messages (LOG_DEBUG, LOG_INFO, LOG_WARNING, LOG_ERROR...)
void SetTraceLogLevel(int logLevel);                        // Set the current threshold (minimum) log level
void *MemAlloc(unsigned int size);                          // Internal memory allocator
void *MemRealloc(void *ptr, unsigned int size);             // Internal memory reallocator
void MemFree(void *ptr);                                    // Internal memory free

// Files management functions
unsigned char *LoadFileData(const char *fileName, int *dataSize); // Load file data as byte array (read)
void UnloadFileData(unsigned char *data);                   // Unload file data allocated by LoadFileData()
bool SaveFileData(const char *fileName, void *data, int dataSize); // Save data to file from byte array (write), returns true on success
bool ExportDataAsCode(const unsigned char *data, int dataSize, const char *fileName); // Export data to code (.h), returns true on success
char *LoadFileText(const char *fileName);                   // Load text data from file (read), returns a '\0' terminated string
void UnloadFileText(char *text);                            // Unload file text data allocated by LoadFileText()
bool SaveFileText(const char *fileName, char *text);        // Save text data to file (write), string must be '\0' terminated, returns true on success
//------------------------------------------------------------------

// File system functions
bool FileExists(const char *fileName);                      // Check if file exists
bool DirectoryExists(const char *dirPath);                  // Check if a directory path exists
bool IsFileExtension(const char *fileName, const char *ext); // Check file extension (including point: .png, .wav)
int GetFileLength(const char *fileName);                    // Get file length in bytes (NOTE: GetFileSize() conflicts with windows.h)
const char *GetFileExtension(const char *fileName);         // Get pointer to extension for a filename string (includes dot: '.png')
const char *GetFileName(const char *filePath);              // Get pointer to filename for a path string
const char *GetFileNameWithoutExt(const char *filePath);    // Get filename string without extension (uses static string)
const char *GetDirectoryPath(const char *filePath);         // Get full path for a given fileName with path (uses static string)
const char *GetPrevDirectoryPath(const char *dirPath);      // Get previous directory path for a given path (uses static string)
const char *GetWorkingDirectory(void);                      // Get current working directory (uses static string)
const char *GetApplicationDirectory(void);                  // Get the directory of the running application (uses static string)
int MakeDirectory(const char *dirPath);                     // Create directories (including full path requested), returns 0 on success
bool ChangeDirectory(const char *dir);                      // Change working directory, return true on success
bool IsPathFile(const char *path);                          // Check if a given path is a file or a directory
bool IsFileNameValid(const char *fileName);                 // Check if fileName is valid for the platform/OS
FilePathList LoadDirectoryFiles(const char *dirPath);       // Load directory filepaths
FilePathList LoadDirectoryFilesEx(const char *basePath, const char *filter, bool scanSubdirs); // Load directory filepaths with extension filtering and recursive directory scan. Use 'DIR' in the filter string to include directories in the result
void UnloadDirectoryFiles(FilePathList files);              // Unload filepaths
bool IsFileDropped(void);                                   // Check if a file has been dropped into window
FilePathList LoadDroppedFiles(void);                        // Load dropped filepaths
void UnloadDroppedFiles(FilePathList files);                // Unload dropped filepaths
long GetFileModTime(const char *fileName);                  // Get file modification time (last write time)

// Compression/Encoding functionality
unsigned char *CompressData(const unsigned char *data, int dataSize, int *compDataSize);        // Compress data (DEFLATE algorithm), memory must be MemFree()
unsigned char *DecompressData(const unsigned char *compData, int compDataSize, int *dataSize);  // Decompress data (DEFLATE algorithm), memory must be MemFree()
char *EncodeDataBase64(const unsigned char *data, int dataSize, int *outputSize);               // Encode data to Base64 string, memory must be MemFree()
unsigned char *DecodeDataBase64(const unsigned char *data, int *outputSize);                    // Decode Base64 string data, memory must be MemFree()
unsigned int ComputeCRC32(unsigned char *data, int dataSize);     // Compute CRC32 hash code
unsigned int *ComputeMD5(unsigned char *data, int dataSize);      // Compute MD5 hash code, returns static int[4] (16 bytes)
unsigned int *ComputeSHA1(unsigned char *data, int dataSize);      // Compute SHA1 hash code, returns static int[5] (20 bytes)


//------------------------------------------------------------------------------------
// Input Handling Functions (Module: core)
//------------------------------------------------------------------------------------

// Input-related functions: keyboard
bool IsKeyPressed(int key);                             // Check if a key has been pressed once
bool IsKeyPressedRepeat(int key);                       // Check if a key has been pressed again
bool IsKeyDown(int key);                                // Check if a key is being pressed
bool IsKeyReleased(int key);                            // Check if a key has been released once
bool IsKeyUp(int key);                                  // Check if a key is NOT being pressed
int GetKeyPressed(void);                                // Get key pressed (keycode), call it multiple times for keys queued, returns 0 when the queue is empty
int GetCharPressed(void);                               // Get char pressed (unicode), call it multiple times for chars queued, returns 0 when the queue is empty
void SetExitKey(int key);                               // Set a custom key to exit program (default is ESC)

// Input-related functions: gamepads
bool IsGamepadAvailable(int gamepad);                                        // Check if a gamepad is available
const char *GetGamepadName(int gamepad);                                     // Get gamepad internal name id
bool IsGamepadButtonPressed(int gamepad, int button);                        // Check if a gamepad button has been pressed once
bool IsGamepadButtonDown(int gamepad, int button);                           // Check if a gamepad button is being pressed
bool IsGamepadButtonReleased(int gamepad, int button);                       // Check if a gamepad button has been released once
bool IsGamepadButtonUp(int gamepad, int button);                             // Check if a gamepad button is NOT being pressed
int GetGamepadButtonPressed(void);                                           // Get the last gamepad button pressed
int GetGamepadAxisCount(int gamepad);                                        // Get gamepad axis count for a gamepad
float GetGamepadAxisMovement(int gamepad, int axis);                         // Get axis movement value for a gamepad axis
int SetGamepadMappings(const char *mappings);                                // Set internal gamepad mappings (SDL_GameControllerDB)
void SetGamepadVibration(int gamepad, float leftMotor, float rightMotor, float duration); // Set gamepad vibration for both motors (duration in seconds)

// Input-related functions: mouse
bool IsMouseButtonPressed(int button);                  // Check if a mouse button has been pressed once
bool IsMouseButtonDown(int button);                     // Check if a mouse button is being pressed
bool IsMouseButtonReleased(int button);                 // Check if a mouse button has been released once
bool IsMouseButtonUp(int button);                       // Check if a mouse button is NOT being pressed
int GetMouseX(void);                                    // Get mouse position X
int GetMouseY(void);                                    // Get mouse position Y
Vector2 GetMousePosition(void);                         // Get mouse position XY
Vector2 GetMouseDelta(void);                            // Get mouse delta between frames
void SetMousePosition(int x, int y);                    // Set mouse position XY
void SetMouseOffset(int offsetX, int offsetY);          // Set mouse offset
void SetMouseScale(float scaleX, float scaleY);         // Set mouse scaling
float GetMouseWheelMove(void);                          // Get mouse wheel movement for X or Y, whichever is larger
Vector2 GetMouseWheelMoveV(void);                       // Get mouse wheel movement for both X and Y
void SetMouseCursor(int cursor);                        // Set mouse cursor

// Input-related functions: touch
int GetTouchX(void);                                    // Get touch position X for touch point 0 (relative to screen size)
int GetTouchY(void);                                    // Get touch position Y for touch point 0 (relative to screen size)
Vector2 GetTouchPosition(int index);                    // Get touch position XY for a touch point index (relative to screen size)
int GetTouchPointId(int index);                         // Get touch point identifier for given index
int GetTouchPointCount(void);                           // Get number of touch points

//------------------------------------------------------------------------------------
// Gestures and Touch Handling Functions (Module: rgestures)
//------------------------------------------------------------------------------------
void SetGesturesEnabled(unsigned int flags);      // Enable a set of gestures using flags
bool IsGestureDetected(unsigned int gesture);     // Check if a gesture have been detected
int GetGestureDetected(void);                     // Get latest detected gesture
float GetGestureHoldDuration(void);               // Get gesture hold time in seconds
Vector2 GetGestureDragVector(void);               // Get gesture drag vector
float GetGestureDragAngle(void);                  // Get gesture drag angle
Vector2 GetGesturePinchVector(void);              // Get gesture pinch delta
float GetGesturePinchAngle(void);                 // Get gesture pinch angle

// module: shapes
void DrawPixel(int posX, int posY, Color color);                                                   // Draw a pixel using geometry [Can be slow, use with care]
void DrawPixelV(Vector2 position, Color color);                                                    // Draw a pixel using geometry (Vector version) [Can be slow, use with care]
void DrawText(const char *text, int posX, int posY, int fontSize, Color color);                     // Draw text (using default font)
void DrawLine(int startPosX, int startPosY, int endPosX, int endPosY, Color color);                // Draw a line
void DrawLineV(Vector2 startPos, Vector2 endPos, Color color);                                     // Draw a line (using gl lines)
void DrawLineEx(Vector2 startPos, Vector2 endPos, float thick, Color color);                       // Draw a line (using triangles/quads)
void DrawLineStrip(const Vector2 *points, int pointCount, Color color);                            // Draw lines sequence (using gl lines)
void DrawLineBezier(Vector2 startPos, Vector2 endPos, float thick, Color color);                   // Draw line segment cubic-bezier in-out interpolation
void DrawCircle(int centerX, int centerY, float radius, Color color);                              // Draw a color-filled circle
void DrawCircleSector(Vector2 center, float radius, float startAngle, float endAngle, int segments, Color color);      // Draw a piece of a circle
void DrawCircleSectorLines(Vector2 center, float radius, float startAngle, float endAngle, int segments, Color color); // Draw circle sector outline
void DrawCircleGradient(int centerX, int centerY, float radius, Color inner, Color outer);         // Draw a gradient-filled circle
void DrawCircleV(Vector2 center, float radius, Color color);                                       // Draw a color-filled circle (Vector version)
void DrawCircleLines(int centerX, int centerY, float radius, Color color);                         // Draw circle outline
void DrawCircleLinesV(Vector2 center, float radius, Color color);                                  // Draw circle outline (Vector version)
void DrawEllipse(int centerX, int centerY, float radiusH, float radiusV, Color color);             // Draw ellipse
void DrawEllipseLines(int centerX, int centerY, float radiusH, float radiusV, Color color);        // Draw ellipse outline
void DrawRing(Vector2 center, float innerRadius, float outerRadius, float startAngle, float endAngle, int segments, Color color); // Draw ring
void DrawRingLines(Vector2 center, float innerRadius, float outerRadius, float startAngle, float endAngle, int segments, Color color);    // Draw ring outline
void DrawRectangle(int posX, int posY, int width, int height, Color color);                        // Draw a color-filled rectangle
void DrawRectangleV(Vector2 position, Vector2 size, Color color);                                  // Draw a color-filled rectangle (Vector version)
void DrawRectangleRec(Rectangle rec, Color color);                                                 // Draw a color-filled rectangle
void DrawRectanglePro(Rectangle rec, Vector2 origin, float rotation, Color color);                 // Draw a color-filled rectangle with pro parameters
void DrawRectangleGradientV(int posX, int posY, int width, int height, Color top, Color bottom);   // Draw a vertical-gradient-filled rectangle
void DrawRectangleGradientH(int posX, int posY, int width, int height, Color left, Color right);   // Draw a horizontal-gradient-filled rectangle
void DrawRectangleGradientEx(Rectangle rec, Color topLeft, Color bottomLeft, Color topRight, Color bottomRight); // Draw a gradient-filled rectangle with custom vertex colors
void DrawRectangleLines(int posX, int posY, int width, int height, Color color);                   // Draw rectangle outline
void DrawRectangleLinesEx(Rectangle rec, float lineThick, Color color);                            // Draw rectangle outline with extended parameters
void DrawRectangleRounded(Rectangle rec, float roundness, int segments, Color color);              // Draw rectangle with rounded edges
void DrawRectangleRoundedLines(Rectangle rec, float roundness, int segments, Color color);         // Draw rectangle lines with rounded edges
void DrawRectangleRoundedLinesEx(Rectangle rec, float roundness, int segments, float lineThick, Color color); // Draw rectangle with rounded edges outline
void DrawTriangle(Vector2 v1, Vector2 v2, Vector2 v3, Color color);                                // Draw a color-filled triangle (vertex in counter-clockwise order!)
void DrawTriangleLines(Vector2 v1, Vector2 v2, Vector2 v3, Color color);                           // Draw triangle outline (vertex in counter-clockwise order!)
void DrawTriangleFan(const Vector2 *points, int pointCount, Color color);                          // Draw a triangle fan defined by points (first vertex is the center)
void DrawTriangleStrip(const Vector2 *points, int pointCount, Color color);                        // Draw a triangle strip defined by points
void DrawPoly(Vector2 center, int sides, float radius, float rotation, Color color);               // Draw a regular polygon (Vector version)
void DrawPolyLines(Vector2 center, int sides, float radius, float rotation, Color color);          // Draw a polygon outline of n sides
void DrawPolyLinesEx(Vector2 center, int sides, float radius, float rotation, float lineThick, Color color); // Draw a polygon outline of n sides with extended parameters

// Splines drawing functions
void DrawSplineLinear(const Vector2 *points, int pointCount, float thick, Color color);                  // Draw spline: Linear, minimum 2 points
void DrawSplineBasis(const Vector2 *points, int pointCount, float thick, Color color);                   // Draw spline: B-Spline, minimum 4 points
void DrawSplineCatmullRom(const Vector2 *points, int pointCount, float thick, Color color);              // Draw spline: Catmull-Rom, minimum 4 points
void DrawSplineBezierQuadratic(const Vector2 *points, int pointCount, float thick, Color color);         // Draw spline: Quadratic Bezier, minimum 3 points (1 control point): [p1, c2, p3, c4...]
void DrawSplineBezierCubic(const Vector2 *points, int pointCount, float thick, Color color);             // Draw spline: Cubic Bezier, minimum 4 points (2 control points): [p1, c2, c3, p4, c5, c6...]
void DrawSplineSegmentLinear(Vector2 p1, Vector2 p2, float thick, Color color);                    // Draw spline segment: Linear, 2 points
void DrawSplineSegmentBasis(Vector2 p1, Vector2 p2, Vector2 p3, Vector2 p4, float thick, Color color); // Draw spline segment: B-Spline, 4 points
void DrawSplineSegmentCatmullRom(Vector2 p1, Vector2 p2, Vector2 p3, Vector2 p4, float thick, Color color); // Draw spline segment: Catmull-Rom, 4 points
void DrawSplineSegmentBezierQuadratic(Vector2 p1, Vector2 c2, Vector2 p3, float thick, Color color); // Draw spline segment: Quadratic Bezier, 2 points, 1 control point
void DrawSplineSegmentBezierCubic(Vector2 p1, Vector2 c2, Vector2 c3, Vector2 p4, float thick, Color color); // Draw spline segment: Cubic Bezier, 2 points, 2 control points

// Spline segment point evaluation functions, for a given t [0.0f .. 1.0f]
Vector2 GetSplinePointLinear(Vector2 startPos, Vector2 endPos, float t);                           // Get (evaluate) spline point: Linear
Vector2 GetSplinePointBasis(Vector2 p1, Vector2 p2, Vector2 p3, Vector2 p4, float t);              // Get (evaluate) spline point: B-Spline
Vector2 GetSplinePointCatmullRom(Vector2 p1, Vector2 p2, Vector2 p3, Vector2 p4, float t);         // Get (evaluate) spline point: Catmull-Rom
Vector2 GetSplinePointBezierQuad(Vector2 p1, Vector2 c2, Vector2 p3, float t);                     // Get (evaluate) spline point: Quadratic Bezier
Vector2 GetSplinePointBezierCubic(Vector2 p1, Vector2 c2, Vector2 c3, Vector2 p4, float t);        // Get (evaluate) spline point: Cubic Bezier

// Basic shapes collision detection functions
bool CheckCollisionRecs(Rectangle rec1, Rectangle rec2);                                           // Check collision between two rectangles
bool CheckCollisionCircles(Vector2 center1, float radius1, Vector2 center2, float radius2);        // Check collision between two circles
bool CheckCollisionCircleRec(Vector2 center, float radius, Rectangle rec);                         // Check collision between circle and rectangle
bool CheckCollisionCircleLine(Vector2 center, float radius, Vector2 p1, Vector2 p2);               // Check if circle collides with a line created betweeen two points [p1] and [p2]
bool CheckCollisionPointRec(Vector2 point, Rectangle rec);                                         // Check if point is inside rectangle
bool CheckCollisionPointCircle(Vector2 point, Vector2 center, float radius);                       // Check if point is inside circle
bool CheckCollisionPointTriangle(Vector2 point, Vector2 p1, Vector2 p2, Vector2 p3);               // Check if point is inside a triangle
bool CheckCollisionPointLine(Vector2 point, Vector2 p1, Vector2 p2, int threshold);                // Check if point belongs to line created between two points [p1] and [p2] with defined margin in pixels [threshold]
bool CheckCollisionPointPoly(Vector2 point, const Vector2 *points, int pointCount);                // Check if point is within a polygon described by array of vertices
bool CheckCollisionLines(Vector2 startPos1, Vector2 endPos1, Vector2 startPos2, Vector2 endPos2, Vector2 *collisionPoint); // Check the collision between two lines defined by two points each, returns collision point by reference
Rectangle GetCollisionRec(Rectangle rec1, Rectangle rec2);                                         // Get collision rectangle for two rectangles collision

