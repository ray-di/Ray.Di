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
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @param callable                 $moduleProvider
     * @param \Ray\Aop\AbstractMatcher $key
     * @param ModuleStringerInterface  $tmpDir
     */
    public function __construct(callable $moduleProvider, $key, $tmpDir)
    {
        $this->moduleProvider = $moduleProvider;
        $this->cacheKey = $key;
        $this->tmpDir = $tmpDir;
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
