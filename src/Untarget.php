<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class Untarget
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * @var string
     */
    private $scope = Scope::PROTOTYPE;

    public function __construct(string $class)
    {
        $this->class = new \ReflectionClass($class);
    }

    /**
     * Bind untargeted binding
     *
     * @param Container $container
     * @param Bind      $bind
     */
    public function __invoke(Container $container, Bind $bind)
    {
        $bound = (new DependencyFactory)->newAnnotatedDependency($this->class);
        $bound->setScope($this->scope);
        $bind->setBound($bound);
        $container->add($bind);
        $constructor = $this->class->getConstructor();
        if ($constructor) {
            (new UntargetedBind)->__invoke($container, $constructor);
        }
    }

    public function setScope(string $scope)
    {
        $this->scope = $scope;
    }
}
