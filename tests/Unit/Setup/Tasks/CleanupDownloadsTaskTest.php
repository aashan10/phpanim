<?php

use App\Commands\SetupCommand;
use App\Setup\Context;
use App\Setup\Tasks\CleanupDownloadsTask;

beforeEach(function () {
    $this->task = new CleanupDownloadsTask;
    $this->mockIo = $this->mock(SetupCommand::class);

    $this->context = new Context(
        arch: null,
        os: null,
        io: $this->mockIo,
        force: false,
        raylibVersion: 'latest',
        extractedPath: null
    );

    $this->testPath = sys_get_temp_dir().'/test_cleanup_'.uniqid();
});

afterEach(function () {
    if (isset($this->testPath) && is_dir($this->testPath)) {
        deleteTestDirectory($this->testPath);
    }
});

it('has correct task name', function () {
    expect($this->task->getName())
        ->toBe('Cleanup Downloads');
});

it('does nothing when extracted path is null', function () {
    $result = $this->task->execute($this->context);

    expect($result)->toBe($this->context);
});

it('does nothing when extracted path does not exist', function () {
    $contextWithNonExistentPath = $this->context->with(['extractedPath' => '/non/existent/path']);

    $result = $this->task->execute($contextWithNonExistentPath);

    expect($result)->toBe($contextWithNonExistentPath);
});

it('deletes extracted directory when it exists', function () {
    mkdir($this->testPath, 0755, true);

    expect(is_dir($this->testPath))->toBeTrue();

    $contextWithExtractedPath = $this->context->with(['extractedPath' => $this->testPath]);
    $result = $this->task->execute($contextWithExtractedPath);

    expect(is_dir($this->testPath))->toBeFalse();
    expect($result)->toBe($contextWithExtractedPath);
});

it('recursively deletes directory with subdirectories', function () {
    mkdir($this->testPath, 0755, true);
    mkdir($this->testPath.'/subdir1', 0755, true);
    mkdir($this->testPath.'/subdir2', 0755, true);
    mkdir($this->testPath.'/subdir2/nested', 0755, true);

    expect(is_dir($this->testPath))->toBeTrue();
    expect(is_dir($this->testPath.'/subdir1'))->toBeTrue();
    expect(is_dir($this->testPath.'/subdir2/nested'))->toBeTrue();

    $contextWithExtractedPath = $this->context->with(['extractedPath' => $this->testPath]);
    $result = $this->task->execute($contextWithExtractedPath);

    expect(is_dir($this->testPath))->toBeFalse();
    expect($result)->toBe($contextWithExtractedPath);
});

it('deletes directory with files', function () {
    mkdir($this->testPath, 0755, true);
    mkdir($this->testPath.'/subdir', 0755, true);
    file_put_contents($this->testPath.'/file1.txt', 'content1');
    file_put_contents($this->testPath.'/subdir/file2.txt', 'content2');

    expect(is_dir($this->testPath))->toBeTrue();
    expect(file_exists($this->testPath.'/file1.txt'))->toBeTrue();
    expect(file_exists($this->testPath.'/subdir/file2.txt'))->toBeTrue();

    $contextWithExtractedPath = $this->context->with(['extractedPath' => $this->testPath]);
    $result = $this->task->execute($contextWithExtractedPath);

    expect(is_dir($this->testPath))->toBeFalse();
    expect($result)->toBe($contextWithExtractedPath);
});

it('preserves other context properties', function () {
    mkdir($this->testPath, 0755, true);

    $contextWithExtractedPath = $this->context->with(['extractedPath' => $this->testPath]);
    $result = $this->task->execute($contextWithExtractedPath);

    expect($result->force)->toBe($this->context->force);
    expect($result->raylibVersion)->toBe($this->context->raylibVersion);
    expect($result->io)->toBe($this->context->io);
});

it('handles empty directory correctly', function () {
    mkdir($this->testPath, 0755, true);

    expect(is_dir($this->testPath))->toBeTrue();

    $contextWithExtractedPath = $this->context->with(['extractedPath' => $this->testPath]);
    $result = $this->task->execute($contextWithExtractedPath);

    expect(is_dir($this->testPath))->toBeFalse();
    expect($result)->toBe($contextWithExtractedPath);
});

function deleteTestDirectory(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }

    $files = array_diff(scandir($dir) ?: [], ['.', '..']);
    foreach ($files as $file) {
        $filePath = $dir.DIRECTORY_SEPARATOR.$file;
        if (is_dir($filePath)) {
            deleteTestDirectory($filePath);
        } else {
            unlink($filePath);
        }
    }
    rmdir($dir);
}
