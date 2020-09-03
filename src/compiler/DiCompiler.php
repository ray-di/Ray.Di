<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\Container;
use Ray\Di\Injector;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;

final class DiCompiler implements InjectorInterface
{
    /**
     * @var string
     */
    private $scriptDir;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var DependencyCode
     */
    private $dependencyCompiler;

    /**
     * @var Injector
     */
    private $injector;

    /**
     * @var null|AbstractModule
     */
    private $module;

    /**
     * @var DependencySaver
     */
    private $dependencySaver;

    /**
     * @var FilePutContents
     */
    private $filePutContents;

    public function __construct(AbstractModule $module = null, string $scriptDir = '')
    {
        $this->scriptDir = $scriptDir ?: \sys_get_temp_dir();
        $this->container = $module ? $module->getContainer() : new Container;
        $this->injector = new Injector($module, $scriptDir);
        $this->dependencyCompiler = new DependencyCode($this->container);
        $this->module = $module;
        $this->dependencySaver = new DependencySaver($scriptDir);
        $this->filePutContents = new FilePutContents;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $this->compile();

        return (new ScriptInjector($this->scriptDir))->getInstance($interface, $name);
    }

    /**
     * Compile all dependencies in container
     */
    public function compile() : void
    {
        $container = $this->container->getContainer();
        foreach ($container as $dependencyIndex => $dependency) {
            $code = $this->dependencyCompiler->getCode($dependency);
            ($this->dependencySaver)($dependencyIndex, $code);
        }
        $this->savePointcuts($this->container);
        ($this->filePutContents)($this->scriptDir . ScriptInjector::MODULE, \serialize($this->module));
    }

    public function dumpGraph() : void
    {
        $dumper = new GraphDumper($this->container, $this->scriptDir);
        $dumper();
    }

    public function savePointcuts(Container $container) : void
    {
        $ref = (new \ReflectionProperty($container, 'pointcuts'));
        $ref->setAccessible(true);
        $pointcuts = $ref->getValue($container);
        ($this->filePutContents)($this->scriptDir . ScriptInjector::AOP, \serialize($pointcuts));
    }
}
