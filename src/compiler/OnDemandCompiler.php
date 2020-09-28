<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Aop\Compiler;
use Ray\Aop\Pointcut;
use Ray\Compiler\Exception\Unbound;
use Ray\Di\AbstractModule;
use Ray\Di\Bind;
use Ray\Di\Dependency;
use Ray\Di\Exception\NotFound;

final class OnDemandCompiler
{
    /**
     * @var string
     */
    private $scriptDir;

    /**
     * @var ScriptInjector
     */
    private $injector;

    /**
     * @var AbstractModule
     */
    private $module;

    public function __construct(ScriptInjector $injector, string $sctiptDir, AbstractModule $module)
    {
        $this->scriptDir = $sctiptDir;
        $this->injector = $injector;
        $this->module = $module;
    }

    /**
     * Compile dependency on demand
     */
    public function __invoke(string $dependencyIndex) : void
    {
        [$class] = \explode('-', $dependencyIndex);
        $containerObject = $this->module->getContainer();
        try {
            new Bind($containerObject, $class);
        } catch (NotFound $e) {
            throw new Unbound($dependencyIndex, 0, $e);
        }
        $containerArray = $containerObject->getContainer();
        if (! isset($containerArray[$dependencyIndex])) {
            throw new Unbound($dependencyIndex, 0);
        }
        $dependency = $containerArray[$dependencyIndex];
        $pointCuts = $this->loadPointcuts();
        if ($dependency instanceof Dependency && \is_array($pointCuts)) {
            $dependency->weaveAspects(new Compiler($this->scriptDir), $pointCuts);
        }
        $code = (new DependencyCode($containerObject, $this->injector))->getCode($dependency);
        (new DependencySaver($this->scriptDir))($dependencyIndex, $code);
    }

    /**
     * @return array<Pointcut>|false
     */
    private function loadPointcuts()
    {
        $pointcutsPath = $this->scriptDir . ScriptInjector::AOP;
        if (! \file_exists($pointcutsPath)) {
            return false;
        }
        $serialized = \file_get_contents($pointcutsPath);
        assert(! is_bool($serialized));
        $er = error_reporting(error_reporting() ^ E_NOTICE);
        $pointcuts = \unserialize($serialized, ['allowed_classes' => true]);
        error_reporting($er);

        return $pointcuts;
    }
}
