<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\Compiler;
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
        AbstractModule $module = null,
        $tmpDir = null
    ) {
        if (! $this->container) {
            $this->container = new Container;
        }
        $this->matcher = new Matcher;
        $this->configure();
        if ($module) {
            $this->container->merge($module->getContainer());
        }
        $tmpDir = $tmpDir ?: sys_get_temp_dir();
        $this->container->weaveAspects(new Compiler($tmpDir));
    }

    abstract protected function configure();

    /**
     * @param string $interface
     *
     * @return Bind
     */
    protected function bind($interface = '')
    {
        $bind = new Bind($this->container, $interface);

        return $bind;
    }

    /**
     * @param AbstractModule $module
     */
    public function install(AbstractModule $module)
    {
        $this->container->merge($module->getContainer());
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
        return $this->container;
    }

    /**
     * @param AbstractMatcher $classMatcher
     * @param AbstractMatcher $methodMatcher
     * @param array           $interceptors
     */
    public function bindInterceptors(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $pointcut = new Pointcut($classMatcher, $methodMatcher, $interceptors);
        $this->container->addPointcut($pointcut);
    }
}
