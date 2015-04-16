<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\Cache;
use Ray\Aop\Compiler;

class JitBinder
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * Aop class dir
     *
     * @var string
     */
    private $classDir;

    /**
     * @param Container $container
     * @param Cache     $cache
     * @param string    $classDir
     */
    public function __construct(Container $container, Cache $cache, $classDir)
    {
        $this->container = $container;
        $this->cache = $cache;
        $this->classDir = $classDir;
    }

    public function bind($class)
    {
        $classId = str_replace('\\' ,'_', $class);
        $untargetCache = $this->cache->fetch($classId);
        $index = "{$class}-*";
        if ($untargetCache) {
            $this->container[$index] = $untargetCache;

            return;
        }
        $this->JitBind($class);
        //error_log("ray.di jit:{$class}");
        $this->cache->save($classId, $this->container[$index]);
    }

    /**
     * @param string $class
     */
    private function JitBind($class)
    {
        $bind = new Bind($this->container, $class);
        /** @var $bound Dependency */
        $bound = $bind->getBound();
        $this->container->weaveAspect(new Compiler($this->classDir), $bound)->getInstance($class ,Name::ANY);
    }

}
