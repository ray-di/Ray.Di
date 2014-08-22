<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\Cache;
use Ray\Di\Module\ModuleCacheModule;

class ModuleCacheInjector
{

    public static function create(callable $moduleProvider, Cache $cache, $cacheKey, $tmpDir)
    {
        $module = new ModuleCacheModule($moduleProvider, $cacheKey, $tmpDir);
        $injector = Injector::create([$module], $cache, $tmpDir)->enableBindCache();

        return $injector;
    }

}
