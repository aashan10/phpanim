<?php

declare(strict_types=1);

namespace Aashan\Phpanim;

use Aashan\Phpanim\Commands\ExportCommand;
use Aashan\Phpanim\Commands\RenderCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Phpanim extends Application
{
    public function __construct()
    {
        $version = '1.0.0';
        parent::__construct('Phpanim', $version);

        $this->addCommand(new RenderCommand());
        $this->addCommand(new ExportCommand());
    }

    public function getDefinition(): InputDefinition
    {
        $definition = parent::getDefinition();

        $defaultDll = match (PHP_OS_FAMILY) {
            'Windows' => __DIR__ . '/../lib/libraylib.dll',
            'Darwin' => __DIR__ . '/../lib/libraylib.dylib',
            'Linux' => __DIR__ . '/../lib/libraylib.so',
            default => null,
        };

        $definition->addOption(new InputOption(
            name: '--raylib-dll-path',
            shortcut: 'r',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Path to the raylib dynamic library',
            default: $defaultDll,
        ));

        $definition->addOption(new InputOption(
            name: '--raylib-definition-path',
            shortcut: 'd',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Path to the raylib header file',
            default: __DIR__ . '/../lib/raylib.h',
        ));

        $definition->addOption(new InputOption(
            name: '--plugin-path',
            shortcut: 'p',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Path(s) to plugin files or directories',
            default: getcwd(),
        ));

        return $definition;
    }
}
