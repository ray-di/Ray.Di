<?php

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Di\Exception\Untargetted;

class Injector implements InjectorInterface
{
    /**
     * @var string
     */
    private $classDir;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param AbstractModule $module
     * @param string         $classDir
     */
    public function __construct(AbstractModule $module = null, string $classDir = null)
    {
        if (is_null($module)) {
            $module = new NullModule;
        }
        $module->install(new AssistedModule);
        $this->container = $module->getContainer();
        $this->classDir = $classDir ?: sys_get_temp_dir();
        $this->container->weaveAspects(new Compiler($this->classDir));

        // builtin injection
        (new Bind($this->container, InjectorInterface::class))->toInstance($this);
    }

    /**
     * Wakeup
     */
    public function __wakeup()
    {
        spl_autoload_register(
            function ($class) {
                $file = $this->classDir . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($file)) {
                    // @codeCoverageIgnoreStart
                    include $file;
                    // @codeCoverageIgnoreEnd
                }
            }
        );
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        try {
            $instance = $this->container->getInstance($interface, $name);
        } catch (Untargetted $e) {
            $this->bind($interface);
            $instance = $this->getInstance($interface, $name);
        }

        return $instance;
    }

    /**
     * @param string $class
     */
    private function bind(string $class)
    {
        new Bind($this->container, $class);
        /* @var $bound Dependency */
        $bound = $this->container->getContainer()[$class . '-' . Name::ANY];
        if ($bound instanceof Dependency) {
            $this->container->weaveAspect(new Compiler($this->classDir), $bound)->getInstance($class, Name::ANY);
        }
    }
}
