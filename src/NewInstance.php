<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;

final class NewInstance
{
    /**
     * @var string
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
     * @var AspectBind
     */
    private $bind;

    public function __construct(
        \ReflectionClass $class,
        SetterMethods $setterMethods,
        Name $constructorName = null
    ) {
        $constructorName = $constructorName ?: new Name(Name::ANY);
        $this->class = $class->name;
        $constructor = $class->getConstructor();
        if ($constructor) {
            $this->arguments = new Arguments($constructor, $constructorName);
        }
        $this->setterMethods = $setterMethods;
    }

    /**
     * @throws \ReflectionException
     */
    public function __invoke(Container $container)
    {
        $instance = $this->arguments instanceof Arguments ? (new \ReflectionClass($this->class))->newInstanceArgs($this->arguments->inject($container)) : new $this->class;

        return $this->postNewInstance($container, $instance);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->class;
    }

    /**
     * @throws \ReflectionException
     */
    public function newInstanceArgs(Container $container, array $params)
    {
        $instance = (new \ReflectionClass($this->class))->newInstanceArgs($params);

        return $this->postNewInstance($container, $instance);
    }

    /**
     * @param string $class
     */
    public function weaveAspects($class, AopBind $bind)
    {
        $this->class = $class;
        $this->bind = new AspectBind($bind);
    }

    private function postNewInstance(Container $container, $instance)
    {
        // setter injection
        ($this->setterMethods)($instance, $container);

        // bind dependency injected interceptors
        if ($this->bind instanceof AspectBind) {
            $instance->bindings = $this->bind->inject($container);
        }

        return $instance;
    }
}
