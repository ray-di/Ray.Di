<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\InvalidToConstructorNameParameter;

final class Bind
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     * @phpstan-var class-string<\Ray\Aop\MethodInterceptor>|string
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
     * @var null|Untarget
     */
    private $untarget;

    /**
     * @param Container                                       $container dependency container
     * @param class-string<\Ray\Aop\MethodInterceptor>|string $interface interface or concrete class name
     */
    public function __construct(Container $container, string $interface)
    {
        $this->container = $container;
        $this->interface = $interface;
        $this->validate = new BindValidator;
        $bindUntarget = class_exists($interface) && ! (new \ReflectionClass($interface))->isAbstract() && ! $this->isRegistered($interface);
        $this->bound = new NullDependency;
        if ($bindUntarget) {
            assert(class_exists($interface));
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
        $refClass = $this->validate->to($this->interface, $class);
        $this->bound = (new DependencyFactory)->newAnnotatedDependency($refClass);
        $this->container->add($this);

        return $this;
    }

    /**
     * Bind to constructor
     *
     * @param string          $class           class name
     * @param string | array  $name            "varName=bindName,..." or [[$varName => $bindName],[$varName => $bindName]...]
     * @param InjectionPoints $injectionPoints injection points
     * @param string          $postConstruct   method name of initialization after all dependencies are injected*
     */
    public function toConstructor(string $class, $name, InjectionPoints $injectionPoints = null, string $postConstruct = null) : self
    {
        if (\is_array($name)) {
            $name = $this->getStringName($name);
        }
        $this->untarget = null;
        $postConstructRef = $postConstruct ? new \ReflectionMethod($class, $postConstruct) : null;
        assert(class_exists($class));
        $this->bound = (new DependencyFactory)->newToConstructor(new \ReflectionClass($class), $name, $injectionPoints, $postConstructRef);
        $this->container->add($this);

        return $this;
    }

    /**
     * Bind to provider
     *
     * @phpstan-param class-string $provider
     */
    public function toProvider(string $provider, string $context = '') : self
    {
        $this->untarget = null;
        $refClass = $this->validate->toProvider($provider);
        $this->bound = (new DependencyFactory)->newProvider($refClass, $context);
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

    public function setBound(DependencyInterface $bound) : void
    {
        $this->bound = $bound;
    }

    private function isRegistered(string $interface) : bool
    {
        return isset($this->container->getContainer()[$interface . '-' . Name::ANY]);
    }

    /**
     * Return string
     *
     * input: ['varA' => 'nameA', 'varB' => 'nameB']
     * output: "varA=nameA,varB=nameB"
     */
    private function getStringName(array $name) : string
    {
        $keys = array_keys($name);

        $names = array_reduce(
            $keys,
            /**
             * @param array-key $key
             */
            function (array $carry, $key) use ($name) : array {
                if (! is_string($key)) {
                    throw new InvalidToConstructorNameParameter((string) $key);
                }
                $varName = $name[$key];
                if (! is_string($varName)) {
                    throw new InvalidToConstructorNameParameter(print_r($varName, true));
                }
                $carry[] = $key . '=' . $varName;

                return $carry;
            },
            []
        );

        return implode(',', $names);
    }
}
