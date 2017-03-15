<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

class Untarget
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * @var string
     */
    private $scope = Scope::PROTOTYPE;

    /**
     * @param string $class
     */
    public function __construct($class)
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

    public function setScope($scope)
    {
        $this->scope = $scope;
    }
}
