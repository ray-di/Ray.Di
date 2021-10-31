<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\Pointcut;
use Ray\Aop\PriorityPointcut;

use function assert;
use function class_exists;
use function interface_exists;

abstract class AbstractModule
{
    /** @var Matcher */
    protected $matcher;

    /** @var ?AbstractModule */
    protected $lastModule;

    /** @var ?Container */
    private $container;

    public function __construct(
        ?self $module = null
    ) {
        $this->lastModule = $module;
        $this->activate();
        assert($this->container instanceof Container);
        if ($module instanceof self) {
            $this->container->merge($module->getContainer());
        }
    }

    public function __toString(): string
    {
        return (new ModuleString())($this->getContainer(), $this->getContainer()->getPointcuts());
    }

    /**
     * Install module
     */
    public function install(self $module): void
    {
        $this->getContainer()->merge($module->getContainer());
    }

    /**
     * Override module
     */
    public function override(self $module): void
    {
        $module->getContainer()->merge($this->getContainer());
        $this->container = $module->getContainer();
    }

    /**
     * Return activated container
     */
    public function getContainer(): Container
    {
        if (! $this->container instanceof Container) {
            $this->activate();
            assert($this->container instanceof Container);
        }

        return $this->container;
    }

    /**
     * Bind interceptor
     *
     * @param array<class-string<MethodInterceptor>> $interceptors
     */
    public function bindInterceptor(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors): void
    {
        $pointcut = new Pointcut($classMatcher, $methodMatcher, $interceptors);
        $this->getContainer()->addPointcut($pointcut);
        foreach ($interceptors as $interceptor) {
            if (class_exists($interceptor)) {
                (new Bind($this->getContainer(), $interceptor))->to($interceptor)->in(Scope::SINGLETON);

                return;
            }

            assert(interface_exists($interceptor));
            (new Bind($this->getContainer(), $interceptor))->in(Scope::SINGLETON);
        }
    }

    /**
     * Bind interceptor early
     *
     * @param array<class-string<MethodInterceptor>> $interceptors
     */
    public function bindPriorityInterceptor(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors): void
    {
        $pointcut = new PriorityPointcut($classMatcher, $methodMatcher, $interceptors);
        $this->getContainer()->addPointcut($pointcut);
        foreach ($interceptors as $interceptor) {
            (new Bind($this->getContainer(), $interceptor))->to($interceptor)->in(Scope::SINGLETON);
        }
    }

    /**
     * Rename binding name
     *
     * @param string $interface       Interface
     * @param string $newName         New binding name
     * @param string $sourceName      Original binding name
     * @param string $targetInterface Original interface
     */
    public function rename(string $interface, string $newName, string $sourceName = Name::ANY, string $targetInterface = ''): void
    {
        $targetInterface = $targetInterface ?: $interface;
        if ($this->lastModule instanceof self) {
            $this->lastModule->getContainer()->move($interface, $sourceName, $targetInterface, $newName);
        }
    }

    /**
     * Configure binding
     *
     * @return void
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    abstract protected function configure();

    /**
     * Bind interface
     *
     * @phpstan-param class-string|string $interface
     */
    protected function bind(string $interface = ''): Bind
    {
        return new Bind($this->getContainer(), $interface);
    }

    /**
     * Activate bindings
     */
    private function activate(): void
    {
        $this->container = new Container();
        $this->matcher = new Matcher();
        $this->configure();
    }
}
