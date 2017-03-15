<?php
namespace Ray\Di;

class FakeToBindInvalidClassModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('\Ray\Di\Mock\Dependency\RobotInterface')->to('InvalidClassXXX');
    }
}
