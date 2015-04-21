<?php

/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class ContainerCompiler
{
    /**
     * @var DependencyCompiler
     */
    private $dependencyCompiler;

    public function __construct(DependencyCompiler $dependencyCompiler)
    {
        $this->dependencyCompiler = $dependencyCompiler;
    }

    public function compile(Container $container)
    {
        $dependencies = $container->getContainer();
        foreach($dependencies as $dependencyIndex => $dependency)
        {
            $this->dependencyCompiler->compile($dependency);
        }
        $container->accept($container);
    }
}
