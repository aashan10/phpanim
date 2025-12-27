<?php

use App\Commands\SetupCommand;
use App\Enums\CpuArchitecture;
use App\Enums\OperatingSystem;
use App\Setup\Tasks\TaskInterface;

beforeEach(function () {
    // Create mock tasks for testing
    $this->mockTask1 = $this->mock(TaskInterface::class);
    $this->mockTask2 = $this->mock(TaskInterface::class);

    // Bind a test version of SetupCommand with mock tasks
    $this->app->bind(SetupCommand::class, function () {
        return new SetupCommand([$this->mockTask1, $this->mockTask2]);
    });
});

it('executes setup command with default parameters', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup')
        ->assertExitCode(0);
});

it('accepts force flag', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        expect($context->force)->toBeTrue();

        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup --force')
        ->assertExitCode(0);
});

it('accepts raylib version parameter', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        expect($context->raylibVersion)->toBe('5.0');

        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup --raylib-version=5.0')
        ->assertExitCode(0);
});

it('accepts operating system parameter', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        expect($context->os)->toBe(OperatingSystem::LINUX);

        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup --os=linux')
        ->assertExitCode(0);
});

it('accepts cpu architecture parameter', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        expect($context->arch)->toBe(CpuArchitecture::ARM64);

        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup --arch=arm64')
        ->assertExitCode(0);
});

it('accepts all parameters together', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        expect($context->force)->toBeTrue();
        expect($context->raylibVersion)->toBe('5.0');
        expect($context->os)->toBe(OperatingSystem::MACOS);
        expect($context->arch)->toBe(CpuArchitecture::ARM64);

        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup --force --raylib-version=5.0 --os=macos --arch=arm64')
        ->assertExitCode(0);
});

it('handles invalid operating system gracefully', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        expect($context->os)->toBeNull();

        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup --os=invalid')
        ->assertExitCode(0);
});

it('handles invalid cpu architecture gracefully', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Mock Task 1');
    $this->mockTask1->shouldReceive('execute')->andReturnUsing(function ($context) {
        expect($context->arch)->toBeNull();

        return $context;
    });

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup --arch=invalid')
        ->assertExitCode(0);
});

it('handles task execution failures gracefully', function () {
    $this->mockTask1->shouldReceive('getName')->andReturn('Failing Task');
    $this->mockTask1->shouldReceive('execute')->andThrow(new \RuntimeException('Test failure'));

    $this->mockTask2->shouldReceive('getName')->andReturn('Mock Task 2');
    $this->mockTask2->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->artisan('setup')
        ->assertExitCode(0);
});

it('works with empty tasks array', function () {
    // Override with empty tasks
    $this->app->bind(SetupCommand::class, function () {
        return new SetupCommand([]);
    });

    $this->artisan('setup')
        ->assertExitCode(0);
});

it('skips non-task objects in tasks array', function () {
    // Override with mixed array
    $validTask = $this->mock(TaskInterface::class);
    $validTask->shouldReceive('getName')->andReturn('Valid Task');
    $validTask->shouldReceive('execute')->andReturnUsing(function ($context) {
        return $context;
    });

    $this->app->bind(SetupCommand::class, function () use ($validTask) {
        return new SetupCommand([new \stdClass, $validTask]);
    });

    $this->artisan('setup')
        ->assertExitCode(0);
});
