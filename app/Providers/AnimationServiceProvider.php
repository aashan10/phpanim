<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\OperatingSystem;
use App\Support\ConfigPath;
use Illuminate\Support\ServiceProvider;
use Phpanim\Animations\AnimationQueue;
use Phpanim\Events\EventDispatcher;
use Phpanim\Raylib\Raylib;

class AnimationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EventDispatcher::class, function () {
            return new EventDispatcher;
        });

        $this->app->singleton(AnimationQueue::class, function () {
            return new AnimationQueue;
        });

        $os = OperatingSystem::detect();

        if (file_exists(ConfigPath::raylibPath('raylib.h')) && file_exists($os->dllPath())) {
            $this->app->singleton(Raylib::class, function () use ($os) {

                $dllName = $os->dllPath();
                $headerPath = ConfigPath::raylibPath('raylib.h');

                $ffi = \FFI::cdef(code: file_get_contents($headerPath), lib: $dllName);

                return new Raylib($ffi);
            });
        }

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
