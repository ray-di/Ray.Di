<?php

declare(strict_types=1);

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
        assert(class_exists($class));
        $this->class = new \ReflectionClass($class);
    }

    /**
     * Bind untargeted binding
     */
    public function __invoke(Container $container, Bind $bind) : void
    {
        $bound = (new DependencyFactory)->newAnnotatedDependency($this->class);
        $bound->setScope($this->scope);
        $bind->setBound($bound);
        $container->add($bind);
        $constructor = $this->class->getConstructor();
        if ($constructor) {
            (new UntargetedBind)($container, $constructor);
        }
    }

    public function setScope(string $scope) : void
    {
        $this->scope = $scope;
    }
}
