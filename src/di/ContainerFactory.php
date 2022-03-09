<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Di\MultiBinding\MultiBindingModule;

use function array_merge;
use function array_shift;
use function is_array;

final class ContainerFactory
{
    /**
     * @param AbstractModule|non-empty-array<AbstractModule>|null $module Module(s)
     */
    public function __invoke($module, string $classDir): Container
    {
        $module = $module ?? new NullModule();
        $builtInModules = [
            new AssistedModule(),
            new ProviderSetModule(),
            new MultiBindingModule(),
        ];
        $modules = array_merge($builtInModules, is_array($module) ? $module : [$module]);
        $baseModule = array_shift($modules);
        foreach ($modules as $module) {
            $baseModule->install($module);
        }

        $container = $baseModule->getContainer();
        $container->map(static function (DependencyInterface $dependency) use ($classDir) {
            if ($dependency instanceof NullObjectDependency) {
                return $dependency->toNull($classDir);
            }

            return $dependency;
        });
        $container->weaveAspects(new Compiler($classDir));

        return $container;
    }
}
