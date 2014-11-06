<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;

abstract class AbstractModule
{
    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param AbstractModule $module
     */
    public function __construct(
        AbstractModule $module = null
    ) {
        $this->activate();
        if ($module) {
            $this->container->merge($module->getContainer());
        }
    }

    abstract protected function configure();

    /**
     * @param string $interface
     *
     * @return Bind
     */
    protected function bind($interface = '')
    {
        $bind = new Bind($this->getContainer(), $interface);

        return $bind;
    }

    /**
     * @param AbstractModule $module
     */
    public function install(AbstractModule $module)
    {
        $this->getContainer()->merge($module->getContainer());
    }

    /**
     * @param AbstractModule $module
     */
    public function overrideInstall(AbstractModule $module)
    {
        $module->getContainer()->merge($this->container);
        $this->container = $module->getContainer();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        if (! $this->container) {
            $this->activate();
        }
        return $this->container;
    }

    /**
     * @param AbstractMatcher $classMatcher
     * @param AbstractMatcher $methodMatcher
     * @param array           $interceptors
     */
    public function bindInterceptor(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $pointcut = new Pointcut($classMatcher, $methodMatcher, $interceptors);
        $this->container->addPointcut($pointcut);
        foreach ($interceptors as $interceptor) {
            $bind = (new Bind($this->container, $interceptor))->to($interceptor)->in(Scope::SINGLETON);
            $this->container->add($bind);
        }
    }

    private  function activate()
    {
        if ($this->container) {
            return;
        }
        $this->container = new Container;
        $this->matcher = new Matcher;
        $this->configure();
    }
}
