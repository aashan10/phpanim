<?php

namespace App\Providers;

use App\Commands\SetupCommand;
use App\Setup\Tasks\CheckPlatformRequirementsTask;
use App\Setup\Tasks\ChoosePlatformSpecificRaylibAssetTask;
use App\Setup\Tasks\CleanupDownloadsTask;
use App\Setup\Tasks\CopyFilesTask;
use App\Setup\Tasks\DownloadRaylibTask;
use App\Setup\Tasks\ExtractArchiveTask;
use App\Setup\Tasks\FetchReleaseMetadataTask;
use App\Setup\Tasks\PreprocessHeaderFileTask;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        $tasks = [
            app()->make(CheckPlatformRequirementsTask::class),
            app()->make(FetchReleaseMetadataTask::class),
            app()->make(ChoosePlatformSpecificRaylibAssetTask::class),
            app()->make(DownloadRaylibTask::class),
            app()->make(ExtractArchiveTask::class),
            app()->make(CopyFilesTask::class),
            app()->make(PreprocessHeaderFileTask::class),
            app()->make(CleanupDownloadsTask::class),
        ];

        $this->app->bind(SetupCommand::class, function ($app) use ($tasks) {
            return new SetupCommand($tasks);
        });
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
