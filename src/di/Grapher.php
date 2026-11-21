<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Compiler;

use function file_exists;
use function spl_autoload_register;
use function sprintf;
use function str_replace;

/**
 * @psalm-import-type MethodArguments from Types
 * @psalm-import-type ScriptDir from Types
 */
final class Grapher
{
    private Container $container;

    /**
     * @param AbstractModule $module   Binding module
     * @param ScriptDir      $classDir Class directory
     */
    public function __construct(AbstractModule $module, private string $classDir)
    {
        $module->install(new AssistedModule());
        $this->container = $module->getContainer();
        /** @psalm-suppress InvalidArgument */
        $this->container->weaveAspects(new Compiler($this->classDir));

        // builtin injection
        (new Bind($this->container, InjectorInterface::class))->toInstance(new Injector($module, $this->classDir));
    }

    /**
     * Wakeup
     */
    public function __wakeup()
    {
        spl_autoload_register(
            function (string $class): void {
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
     * @param string      $class  class name
     * @param list<mixed> $params construct parameters (MethodArguments)
     *
     * @return mixed
     */
    public function newInstanceArgs(string $class, array $params)
    {
        return $this->container->getInstanceWithArgs($class, $params);
    }
}
