<?php

declare(strict_types=1);

namespace Ray\Di;

final class ModuleString
{
    public function __invoke(Container $container, array $pointcuts) : string
    {
        $log = [];
        /** @var Container $container */
        $container = unserialize(serialize($container), ['allowed_classes' => true]);
        $spy = new SpyCompiler;
        foreach ($container->getContainer() as $dependencyIndex => $dependency) {
            if ($dependency instanceof Dependency) {
                $dependency->weaveAspects($spy, $pointcuts);
            }
            $log[] = sprintf(
                '%s => %s',
                $dependencyIndex,
                (string) $dependency
            );
        }
        sort($log);

        return implode(PHP_EOL, $log);
    }
}
