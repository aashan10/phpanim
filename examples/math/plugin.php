<?php

require_once __DIR__ . '/scenes/func_scene.php';

use Aashan\Phpanim\Plugins\SceneManagerPlugin;

$sceneManager = new SceneManagerPlugin();

$sceneManager->addScene('func_scene', new FuncScene());


return $sceneManager;
