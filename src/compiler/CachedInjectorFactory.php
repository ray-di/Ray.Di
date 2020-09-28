<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\VoidCache;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

final class CachedInjectorFactory
{
    /** @var array<string, InjectorInterface> */
    private static $injectors = [];

    private function __construct()
    {
    }

    /**
     * @param callable(): AbstractModule $modules
     * @param array<class-string>        $savedSingletons
     */
    public static function getInstance(string $injectorId, string $scriptDir, callable $modules, ?CacheProvider $cache = null, array $savedSingletons = []): InjectorInterface
    {
        if (isset(self::$injectors[$injectorId])) {
            return self::$injectors[$injectorId];
        }

        $cache ??= new VoidCache();
        $cache->setNamespace($injectorId);
        $cachedInjector = $cache->fetch(InjectorInterface::class);
        if ($cachedInjector instanceof InjectorInterface) {
            return $cachedInjector;
        }

        $injector = self::getInjector($modules, $scriptDir, $savedSingletons);
        if ($injector instanceof ScriptInjector) {
            $cache->save(InjectorInterface::class, $injector);
        }

        self::$injectors[$injectorId] = $injector;

        return $injector;
    }

    /**
     * @param callable(): AbstractModule $modules
     * @param array<class-string>        $savedSingletons
     */
    private static function getInjector(callable $modules, string $scriptDir, array $savedSingletons): InjectorInterface
    {
        $injector = InjectorFactory::getInstance($modules, $scriptDir);
        foreach ($savedSingletons as $singleton) {
            $injector->getInstance($singleton);
        }

        return $injector;
    }
}
