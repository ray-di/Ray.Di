<?php

class ModuleCacheInjector
{

    public static function create(callable $moduleProvider, Cache $cache, $cacheKey, $tmdDir)
    {
        $module = new ModuleCacheModule($moduleProvider, $cacheKey, $tmdDir);
        $injector = Injector::create([$module], $cache, $tmpDir)->enableBindCache();

        return $injector;
    }

}