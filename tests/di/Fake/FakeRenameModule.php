<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeRenameModule extends AbstractModule
{
    protected function configure()
    {
        $this->rename(FakeRobotInterface::class, 'original');
    }
}
