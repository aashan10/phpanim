<?php

declare(strict_types=1);

namespace App\Providers;

use App\Animations\AnimationExecutor;
use App\Enums\OperatingSystem;
use App\Raylib\Raylib;
use App\Support\ConfigPath;
use Illuminate\Support\ServiceProvider;

class AnimationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Raylib::class, function () {
            $os = OperatingSystem::detect();

            $dllName = $os->dllPath();
            $headerPath = ConfigPath::raylibPath('raylib.h');

            $ffi = \FFI::cdef(code: file_get_contents($headerPath), lib: $dllName);
            return new Raylib($ffi);
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
