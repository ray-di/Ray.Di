<?php

namespace Ray\Di;

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
}
