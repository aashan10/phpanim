<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Commands;

use Aashan\Phpanim\Plugins\PluginManager;
use Aashan\Phpanim\Raylib;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    protected Raylib $rl;
    protected PluginManager $pluginManager;

    abstract public function handle(InputInterface $input, OutputInterface $output): int;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dllPath = $input->getOption('raylib-dll-path');
        $definitionPath = $input->getOption('raylib-definition-path');
        $pluginPath = $input->getOption('plugin-path');

        $io = new SymfonyStyle($input, $output);

        if (!file_exists($dllPath)) {
            $io->error("Raylib DLL not found at path: {$dllPath}");
            return Command::FAILURE;
        }

        if (!file_exists($definitionPath)) {
            $io->error("Raylib definition file not found at path: {$definitionPath}");
            return Command::FAILURE;
        }

        if (!is_dir($pluginPath)) {
            $io->error("Plugin path is not a directory: {$pluginPath}");
            return Command::FAILURE;
        }

        $definition = file_get_contents($definitionPath);

        $this->rl = new Raylib($dllPath, $definition);

        $this->pluginManager = new PluginManager($pluginPath);

        try {
            $status = $this->handle($input, $io);
        } catch (\Throwable $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            $status = Command::FAILURE;
        } finally {
            $this->pluginManager->unregister($this->rl);
        }
        return $status;
    }
}
