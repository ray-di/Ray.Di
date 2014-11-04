<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Di\Exception\Unbound;

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
    public function __construct(AbstractModule $module = null, $classDir = null)
    {
        $this->classDir = $classDir ?: sys_get_temp_dir();
        $this->container =  $module ? $module->getContainer() : new Container;
        $this->container->weaveAspects(new Compiler($this->classDir));

        // builtin injection
        (new Bind($this->container, InjectorInterface::class))->toInstance($this);
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
        } catch (Unbound $e) {
            $instance = $this->getInstanceOnDemandBind($interface, $e);
        }

        return $instance;
    }

    /**
     * @param string  $class
     * @param Unbound $e
     *
     * @return mixed
     */
    private function getInstanceOnDemandBind($class, Unbound $e)
    {
        if (! class_exists($class)) {
            throw $e;
        }
        $bind = (new Bind($this->container, $class))->to($class);
        $this->container->add($bind);
        $instance = $this->container->weaveAspect(new Compiler($this->classDir), $bind->getBound())->getInstance($class, Name::ANY);

        return $instance;
    }

    public function __wakeup()
    {
        spl_autoload_register(
            function ($class) {
                $file = $this->classDir . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($file)) {
                    // @codeCoverageIgnoreStart
                    include $file;
                    // @@codeCoverageIgnoreEnd
                }
            }
        );
    }
}
