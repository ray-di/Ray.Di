<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

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
     * @var Untarget|null
     */
    private $untarget;

    /**
     * @param Container $container dependency container
     * @param string    $interface interface or concrete class name
     */
    public function __construct(Container $container, string $interface)
    {
        $this->container = $container;
        $this->interface = $interface;
        $this->validate = new BindValidator;
        $bindUntarget = class_exists($interface) && ! (new \ReflectionClass($interface))->isAbstract() && ! $this->isRegistered($interface);
        if ($bindUntarget) {
            $this->untarget = new Untarget($interface);

            return;
        }
        $this->validate->constructor($interface);
    }

    public function __destruct()
    {
        if ($this->untarget) {
            ($this->untarget)($this->container, $this);
            $this->untarget = null;
        }
    }

    public function __toString()
    {
        return $this->interface . '-' . $this->name;
    }

    /**
     * Set dependency name
     */
    public function annotatedWith(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Bind to class
     */
    public function to(string $class) : self
    {
        $this->untarget = null;
        $this->validate->to($this->interface, $class);
        $this->bound = (new DependencyFactory)->newAnnotatedDependency(new \ReflectionClass($class));
        $this->container->add($this);

        return $this;
    }

    /**
     * Bind to constructor
     *
     * @param string          $class           class name
     * @param string | array  $name            "varName=bindName,..." or [[$varName => $bindName],[$varName => $bindName]...]
     * @param InjectionPoints $injectionPoints injection points
     * @param null            $postConstruct   method name of initialization after all dependencies are injected*
     */
    public function toConstructor(string $class, $name, InjectionPoints $injectionPoints = null, $postConstruct = null) : self
    {
        if (\is_array($name)) {
            $name = $this->getStringName($name);
        }
        $this->untarget = null;
        $postConstruct = $postConstruct ? new \ReflectionMethod($class, $postConstruct) : null;
        $this->bound = (new DependencyFactory)->newToConstructor(new \ReflectionClass($class), $name, $injectionPoints, $postConstruct);
        $this->container->add($this);

        return $this;
    }

    /**
     * Bind to provider
     */
    public function toProvider(string $provider, string $context = '') : self
    {
        $this->untarget = null;
        $this->validate->toProvider($provider);
        $this->bound = (new DependencyFactory)->newProvider(new \ReflectionClass($provider), $context);
        $this->container->add($this);

        return $this;
    }

    /**
     * Bind to instance
     *
     * @param mixed $instance
     */
    public function toInstance($instance) : self
    {
        $this->untarget = null;
        $this->bound = new Instance($instance);
        $this->container->add($this);

        return $this;
    }

    /**
     * Set scope
     */
    public function in(string $scope) : self
    {
        if ($this->bound instanceof Dependency || $this->bound instanceof DependencyProvider) {
            $this->bound->setScope($scope);
        }
        if ($this->untarget) {
            $this->untarget->setScope($scope);
        }

        return $this;
    }

    public function getBound() : DependencyInterface
    {
        return $this->bound;
    }

    public function setBound(DependencyInterface $bound)
    {
        $this->bound = $bound;
    }

    private function isRegistered(string $interface) : bool
    {
        $isRegistered = isset($this->container->getContainer()[$interface . '-' . Name::ANY]);

        return $isRegistered;
    }

    /**
     * Return string
     *
     * input: [['varA' => 'nameA'], ['varB' => 'nameB']]
     * output: "varA=nameA,varB=nameB"
     */
    private function getStringName(array $name) : string
    {
        $names = array_reduce(array_keys($name), function ($carry, $key) use ($name) {
            $carry[] .= $key . '=' . $name[$key];

            return $carry;
        }, []);

        return implode(',', $names);
    }
}
