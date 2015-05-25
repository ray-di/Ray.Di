<?php

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Di\Exception\NotFound;

final class Bind
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var string
     */
    private $name = Name::ANY;

    /**
     * @var DependencyInterface
     */
    private $bound;

    /**
     * @var BindValidator
     */
    private $validate;

    /**
     * @var Untarget
     */
    private $untarget;

    /**
     * @param Container $container dependency container
     * @param string    $interface interface or concrete class name
     */
    public function __construct(Container $container, $interface)
    {
        $this->container = $container;
        $this->interface = $interface;
        $this->validate = new BindValidator;
        $bindUntarget = class_exists($interface) && ! (new \ReflectionClass($interface))->isAbstract() && $this->hasNotRegistered($interface);
        if ($bindUntarget) {
            $this->untarget = new Untarget($interface);

            return;
        }
        $this->validate->constructor($interface);
    }

    public function __destruct()
    {
        if ($this->untarget) {
            $this->untarget->__invoke($this->container, $this);
            $this->untarget = null;
        }
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function annotatedWith($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function to($class)
    {
        $this->untarget = null;
        $this->validate->to($this->interface, $class);
        $this->bound = (new DependencyFactory)->newAnnotatedDependency(new \ReflectionClass($class));
        $this->container->add($this);

        return $this;
    }

    /**
     * @param string          $class           class name
     * @param string          $name            varName=bindName,...
     * @param InjectionPoints $injectionPoints injection points
     * @param null            $postConstruct   method name of initialization after all dependencies are injected
     *
     * @return $this
     */
    public function toConstructor($class, $name, InjectionPoints $injectionPoints = null, $postConstruct = null)
    {
        $this->untarget = null;
        $postConstruct = $postConstruct ? new \ReflectionMethod($class, $postConstruct) : null;
        $this->bound = (new DependencyFactory)->newToConstructor(new \ReflectionClass($class), $name, $injectionPoints, $postConstruct);
        $this->container->add($this);

        return $this;
    }

    /**
     * @param string $provider
     *
     * @return $this
     *
     * @throws NotFound
     */
    public function toProvider($provider)
    {
        $this->untarget = null;
        $this->validate->toProvider($provider);
        $this->bound = (new DependencyFactory)->newProvider(new \ReflectionClass($provider));
        $this->container->add($this);

        return $this;
    }

    /**
     * @param mixed $instance
     *
     * @return $this
     */
    public function toInstance($instance)
    {
        $this->untarget = null;
        $this->bound = new Instance($instance);
        $this->container->add($this);

        return $this;
    }

    /**
     * @param string $scope
     *
     * @return $this
     */
    public function in($scope)
    {
        if ($this->bound instanceof Dependency || $this->bound instanceof DependencyProvider) {
            $this->bound->setScope($scope);
        }
        if ($this->untarget) {
            $this->untarget->setScope($scope);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->interface . '-' . $this->name;
    }

    /**
     * @return DependencyInterface
     */
    public function getBound()
    {
        return $this->bound;
    }

    /**
     * @param DependencyInterface $bound
     */
    public function setBound(DependencyInterface $bound)
    {
        $this->bound = $bound;
    }

    /**
     * @param string $interface
     *
     * @return bool
     */
    private function hasNotRegistered($interface)
    {
        $hasNotRegistered = ! isset($this->container->getContainer()[$interface . '-' . Name::ANY]);

        return $hasNotRegistered;
    }
}
