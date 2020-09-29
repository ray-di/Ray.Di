<?php

declare(strict_types=1);

/**
 * This file is part of the _package_ package
 */

namespace Ray\Di;

class FakeUntargetModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeUntargetChild::class);
    }
}
