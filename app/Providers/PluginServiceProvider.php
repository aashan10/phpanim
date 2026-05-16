<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Phpanim\Plugins\PluginManager;
use Phpanim\Scenes\SceneManager;
use Phpanim\UI\ComponentManager;

class PluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton(SceneManager::class);
        $this->app->singleton(PluginManager::class);
        $this->app->singleton(ComponentManager::class);
    }

    public function register(): void {}
}
