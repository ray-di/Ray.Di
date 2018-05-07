<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class ModuleString
{
    public function __invoke(Container $container, array $pointcuts) : string
    {
        $log = [];
        $contaier = unserialize(serialize($container));
        $spy = new SpyCompiler;
        foreach ($contaier->getContainer() as $dependencyIndex => $dependency) {
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
