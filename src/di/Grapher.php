<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Compiler;

final class Grapher
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
     * @param AbstractModule $module Binding module
     */
    public function __construct(AbstractModule $module, string $classDir)
    {
        $module->install(new AssistedModule);
        $this->container = $module->getContainer();
        $this->classDir = $classDir;
        $this->container->weaveAspects(new Compiler($this->classDir));

        // builtin injection
        (new Bind($this->container, InjectorInterface::class))->toInstance(new Injector($module));
    }

    /**
     * Wakeup
     */
    public function __wakeup()
    {
        spl_autoload_register(
            function (string $class) : void {
                $file = sprintf('%s/%s.php', $this->classDir, str_replace('\\', '_', $class));
                if (file_exists($file)) {
                    include $file; //@codeCoverageIgnore
                }
            }
        );
    }

    /**
     * Build an object graph with give constructor parameters
     *
     * @param string            $class  class name
     * @param array<int, mixed> $params constuct paramteters
     *
     * @return mixed
     */
    public function newInstanceArgs(string $class, array $params)
    {
        return $this->container->getInstanceWithArgs($class, $params);
    }
}
