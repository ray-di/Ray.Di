<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Annotation\Compile;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\Unbound;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

/**
 * @psalm-immutable
 */
final class InjectorFactory
{
    private function __construct()
    {
    }

    /**
     * @param callable():\Ray\Di\AbstractModule $modules
     */
    public static function getInstance(callable $modules, string $scriptDir) : InjectorInterface
    {
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = $modules();
        $rayInjector = new RayInjector($module, $scriptDir);
        $isProd = false;
        try {
            $isProd = $rayInjector->getInstance('', Compile::class);
        } catch (Unbound $e) {
        }

        return $isProd ? self::getScriptInjector($scriptDir, $module) : $rayInjector;
    }

    private static function getScriptInjector(string $scriptDir, AbstractModule $module) : ScriptInjector
    {
        return new ScriptInjector($scriptDir, function () use ($scriptDir, $module) {
            return new ScriptinjectorModule($scriptDir, $module);
        });
    }
}
