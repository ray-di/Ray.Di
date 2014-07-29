<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\Cache;
use Ray\Di\Exception\ModuleProvider;

class CacheableModule extends AbstractModule
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var callable
     */
    private $moduleProvider;

    /**
     * @param callable $moduleProvider
     * @param string   $key
     */
    public function __construct(callable $moduleProvider, $key)
    {
        $this->moduleProvider = $moduleProvider;
        $this->cacheKey = $key;
    }

    /**
     * @param Cache $cache
     *
     * @return callable|
     */
    public function get(Cache $cache)
    {
        $module = $cache->fetch($this->key);
        if ($module) {
            return $module;
        }
        $module = $this->moduleProvider;
        $module = $module();
        $module->activate();
        $cache->save($this->key, $module);

        return $module;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function configure()
    {
    }
}
