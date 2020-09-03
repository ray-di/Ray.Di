<?php

declare(strict_types=1);

namespace Ray\Di;

use ReflectionClass;

final class Untarget
{
    /**
     * @var ReflectionClass<object>
     */
    private $class;

    /**
     * @var string
     */
    private $scope = Scope::PROTOTYPE;

    /**
     * @phpstan-param class-string $class
     */
    public function __construct(string $class)
    {
        $this->class = new ReflectionClass($class);
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
