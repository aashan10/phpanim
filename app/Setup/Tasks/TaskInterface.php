<?php

declare(strict_types=1);

namespace App\Setup\Tasks;

use App\Setup\Context;

interface TaskInterface
{
    public function getName(): string;

    public function execute(Context $context): Context;
}
