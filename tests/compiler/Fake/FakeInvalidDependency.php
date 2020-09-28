<?php

namespace Ray\Compiler;

use Ray\Di\Bind;
use Ray\Di\Container;
use Ray\Di\DependencyInterface;

class FakeInvalidDependency implements DependencyInterface
{
    public function inject(Container $container)
    {
    }

    public function register(array &$container, Bind $bind)
    {
    }

    public function setScope($scope)
    {
    }

    public function __toString()
    {
    }
}
