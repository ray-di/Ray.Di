<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;

final class Untarget
{
    /**
     * @phpstan-var ReflectionClass<object>
     * @psalm-var ReflectionClass
     */
    private $class;

    /** @var string */
    private $scope = Scope::PROTOTYPE;

    /**
     * @param class-string $class
     */
    public function __construct(string $class)
    {
        $this->class = new ReflectionClass($class);
    }

    /**
     * Bind untargeted binding
     */
    public function __invoke(Container $container, Bind $bind): void
    {
        $bound = (new DependencyFactory())->newAnnotatedDependency($this->class);
        $bound->setScope($this->scope);
        $bind->setBound($bound);
        $container->add($bind);
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }
}
