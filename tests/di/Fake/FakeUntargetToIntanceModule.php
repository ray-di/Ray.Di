<?php

declare(strict_types=1);

/**
 * This file is part of the _package_ package
 */

namespace Ray\Di;

class FakeUntargetToIntanceModule extends AbstractModule
{
    protected function configure()
    {
        $instance = new FakeUntarget(new FakeUntargetChild(1));
        $this->bind(FakeUntarget::class)->toInstance($instance);
    }
}
