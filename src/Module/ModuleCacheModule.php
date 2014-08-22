<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Module;

use Doctrine\Common\Cache\Cache;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorFactory;

class ModuleCacheModule extends AbstractModule
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
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @param callable $moduleProvider
     * @param string   $cacheKey
     * @param string   $tmpDir
     */
    public function __construct(callable $moduleProvider, $cacheKey, $tmpDir)
    {
        $this->moduleProvider = $moduleProvider;
        $this->cacheKey = $cacheKey;
        $this->tmpDir = $tmpDir;
    }

    /**
     * @param Cache $cache
     *
     * @return AbstractModule
     */
    public function get(Cache $cache)
    {
        $module = $cache->fetch($this->key);
        if ($module) {

            return $module;
        }
        $module = $this->moduleProvider;
        $module = $module();
        $injector = (new InjectorFactory)->newInstance([$module], $cache, $this->tmpDir);
        $module->activate($injector);
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
