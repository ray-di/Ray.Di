<?php
namespace Ray\Di;

class FakeRenameModule extends AbstractModule
{
    protected function configure()
    {
        $this->rename(FakeRobotInterface::class, 'original');
    }
}
