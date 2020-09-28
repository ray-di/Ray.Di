<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

class FakeProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new DiCompileModule(true));
    }
}
