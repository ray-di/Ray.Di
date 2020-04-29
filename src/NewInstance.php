<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;

final class NewInstance
{
    /**
     * @var class-string
     */
    private $class;

    /**
     * @var SetterMethods
     */
    private $setterMethods;

    /**
     * @var null|Arguments
     */
    private $arguments;

    /**
     * @var ?AspectBind
     */
    private $bind;

    /**
     * @phpstan-param \ReflectionClass<object> $class
     */
    public function __construct(
        \ReflectionClass $class,
        SetterMethods $setterMethods,
        Name $constructorName = null
    ) {
        $constructorName = $constructorName ?: new Name(Name::ANY);
        $this->class = $class->getName();
        $constructor = $class->getConstructor();
        if ($constructor) {
            $this->arguments = new Arguments($constructor, $constructorName);
        }
        $this->setterMethods = $setterMethods;
    }

    /**
     * @throws \ReflectionException
     */
    public function __invoke(Container $container) : object
    {
        $instance = $this->arguments instanceof Arguments ? (new \ReflectionClass($this->class))->newInstanceArgs($this->arguments->inject($container)) : new $this->class;

        return $this->postNewInstance($container, $instance);
    }

    /**
     * @return class-string
     */
    public function __toString()
    {
        return $this->class;
    }

    /**
     * @throws \ReflectionException
     */
    public function newInstanceArgs(Container $container, array $params) : object
    {
        $instance = (new \ReflectionClass($this->class))->newInstanceArgs($params);

        return $this->postNewInstance($container, $instance);
    }

    /**
     * @param class-string $class
     */
    public function weaveAspects(string $class, AopBind $bind) : void
    {
        $this->class = $class;
        $this->bind = new AspectBind($bind);
    }

    private function postNewInstance(Container $container, object $instance) : object
    {
        // setter injection
        ($this->setterMethods)($instance, $container);

        // bind dependency injected interceptors
        if ($this->bind instanceof AspectBind) {
            assert(isset($instance->bindings));
            $instance->bindings = $this->bind->inject($container);
        }

        return $instance;
    }
}
