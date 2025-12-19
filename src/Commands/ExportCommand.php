<?php

declare(strict_types=1);

namespace Aashan\Phpanim\Commands;

use Aashan\Phpanim\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExportCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('export');
        $this->setDescription('Exports the animation into video using ffmpeg.');
    }

    public function handle(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
