<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Aop\Compiler;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\Bind;
use Ray\Di\Container;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;
use ReflectionProperty;

use function assert;
use function is_string;
use function serialize;
use function sys_get_temp_dir;

final class DiCompiler implements InjectorInterface
{
    /** @var string */
    private $scriptDir;

    /** @var Container */
    private $container;

    /** @var DependencyCode */
    private $dependencyCompiler;

    /** @var AbstractModule|null */
    private $module;

    /** @var DependencySaver */
    private $dependencySaver;

    /** @var FilePutContents */
    private $filePutContents;

    public function __construct(AbstractModule $module, string $scriptDir)
    {
        $this->scriptDir = $scriptDir ?: sys_get_temp_dir();
        $this->container = $module->getContainer();
        $this->dependencyCompiler = new DependencyCode($this->container);
        $this->module = $module;
        $this->dependencySaver = new DependencySaver($scriptDir);
        $this->filePutContents = new FilePutContents();
        // Weave AssistedInterceptor and bind InjectorInterface for self
        $module->getContainer()->weaveAspects(new Compiler($scriptDir));
        (new Bind($this->container, InjectorInterface::class))->toInstance($this);
        (new Bind($this->container, ''))->annotatedWith(ScriptDir::class)->toInstance($scriptDir);
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
    public function compile(): void
    {
        $container = $this->container->getContainer();
        $scriptDir = $this->container->getInstance('', ScriptDir::class);
        assert(is_string($scriptDir));
        foreach ($container as $dependencyIndex => $dependency) {
            $code = $this->dependencyCompiler->getCode($dependency, $scriptDir);
            ($this->dependencySaver)($dependencyIndex, $code);
        }

        $this->savePointcuts($this->container);
        ($this->filePutContents)($this->scriptDir . ScriptInjector::MODULE, serialize($this->module));
    }

    public function dumpGraph(): void
    {
        $dumper = new GraphDumper($this->container, $this->scriptDir);
        $dumper();
    }

    public function savePointcuts(Container $container): void
    {
        $ref = (new ReflectionProperty($container, 'pointcuts'));
        $ref->setAccessible(true);
        $pointcuts = $ref->getValue($container);
        ($this->filePutContents)($this->scriptDir . ScriptInjector::AOP, serialize($pointcuts));
    }
}
