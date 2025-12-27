<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Enums\OperatingSystem;
use App\Setup\Context;

readonly final class PreprocessHeaderFileTask implements TaskInterface
{
    public function getName(): string
    {
        return 'Preprocessing Header File';
    }

    public function execute(Context $context): Context
    {
        if ($context->os === OperatingSystem::WINDOWS) {
            $context->io->warn(
                'PHPAnim uses raylib.h for defining FFI interfaces. ' .
                'The default raylib.h file contains stuff like preprocessor macros, comments and conditional compilation directives (#include, #ifdef and single/multi line comments) that are not supported by PHP FFI. '.
                'As of now, we don\'t have an automated way to preprocess the header file on Windows. '.
                'Please preprocess the raylib.h file manually using a C compiler (e.g., `gcc -P -E raylib.h -o raylib.h`)'
            );

            $context->io->info(sprintf('File to be processed: %s', storage_path('raylib/raylib.h')));

            return $context;
        }

        $command = sprintf('cc -P -E %s -o %s',
            escapeshellarg(storage_path('raylib/raylib.h')),
            escapeshellarg(storage_path('raylib/raylib.h'))
        );
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $context->io->error('Failed to preprocess raylib.h file. Please ensure that a C compiler is installed and accessible from the command line.');
            throw new \RuntimeException('Preprocessing raylib.h failed.');
        }
        return $context;
    }
}
