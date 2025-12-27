<?php

namespace App\Commands;

use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use App\Setup\Context;
use App\Setup\Tasks\CheckPlatformRequirementsTask;
use App\Setup\Tasks\ChoosePlatformSpecificRaylibAssetTask;
use App\Setup\Tasks\FetchReleaseMetadataTask;
use App\Setup\Tasks\TaskInterface;
use LaravelZero\Framework\Commands\Command;

class SetupCommand extends Command
{
    protected $signature = 'setup {--f|force : Force setup even if already configured} {--R|raylib-version=latest : Specify the raylib version to install} {--os : Operating System} {--arch : CPU Architecture}';

    protected $description = 'Command description';

    public function handle(): int
    {
        $force         = $this->option('force') ?? false;
        $raylibVersion = $this->option('raylib-version') ?? 'latest';
        $os            = $this->option('os') ?? null;
        $arch          = $this->option('arch') ?? null;

        if ($os !== null) {
            $os = OperatingSystem::tryFrom($os);
        }

        if ($arch !== null) {
            $arch = CpuArchitecture::tryFrom($arch);
        }

        $context = new Context(
            arch:          $arch,
            os:            $os,
            io:            $this,
            force:         $force,
            raylibVersion: $raylibVersion,
        );

        $tasks = [
            app()->make(CheckPlatformRequirementsTask::class),
            app()->make(FetchReleaseMetadataTask::class),
            app()->make(ChoosePlatformSpecificRaylibAssetTask::class),
        ];
        $totalTasks = count($tasks);

        foreach($tasks as $index => $task) {
            if (!$task instanceof TaskInterface) {
                continue;
            }
            $this->task(sprintf("[Step %d of %d]: %s", $index + 1, $totalTasks, $task->getName()), function () use ($task, &$context) {
                try {
                    $context = $task->execute($context);
                    return true;
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                    return false;
                }
            });
        }

        return self::SUCCESS;
    }
}
