<?php

use Phpanim\Plugins\PluginInterface;
use Phpanim\Plugins\SceneManagerPlugin;

require_once __DIR__.'/scenes/SpringScene.php';
require_once __DIR__.'/scenes/PhysicsScene.php';
require_once __DIR__.'/scenes/InertiaScene.php';
require_once __DIR__.'/scenes/MomentumScene.php';

class PhysicsExamplesPlugin implements PluginInterface
{
    private SceneManagerPlugin $sceneManager;

    public function __construct()
    {
        $this->sceneManager = app()->make(SceneManagerPlugin::class);

        $this->sceneManager
            ->addScene('spring', SpringScene::class)
            ->addScene('physics', PhysicsScene::class)
            ->addScene('inertia', InertiaScene::class)
            ->addScene('momentum', MomentumScene::class);

        $this->sceneManager->setSceneOrder(['spring', 'physics', 'inertia', 'momentum']);
    }

    public function register(): void {}

    public function render(): void {}
}

return app()->make(PhysicsExamplesPlugin::class);
