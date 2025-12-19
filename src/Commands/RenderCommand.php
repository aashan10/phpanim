<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Commands;

use Aashan\Phpanim\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RenderCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('render');
        $this->setDescription('Renders the animation.');
    }

    public function handle(InputInterface $input, OutputInterface $output): int
    {
        $this->rl->InitWindow(1600, 900, 'Phpanim Animation');

        $this->pluginManager->initialize($this->rl);

        while (!$this->rl->WindowShouldClose()) {
            $this->rl->BeginDrawing();
            $this->rl->ClearBackground($this->rl->struct('Color', [
                'r' => 255,
                'g' => 255,
                'b' => 255,
                'a' => 255,
            ]));

            $this->pluginManager->update($this->rl);

            $this->rl->EndDrawing();
        }

        $this->rl->CloseWindow();
        return Command::SUCCESS;
    }
}
