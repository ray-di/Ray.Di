<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;

class Locator
{
    /**
     * @var Reader
     */
    private static $annotationReader;

    /**
     * @var Cache
     */
    private static $cache;

    public function setCache(Cache $cache)
    {
        self::$cache = $cache;
        self::$annotationReader = new CachedReader(new AnnotationReader, $cache);

        return $this;
    }

    /**
     * @return Reader
     */
    public function getAnnotationReader()
    {
        if (is_null(self::$annotationReader)) {
            self::$annotationReader = new AnnotationReader;
        }

        return self::$annotationReader;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return self::$cache;
    }

    public function cleaAll()
    {
        self::$cache = null;
        self::$annotationReader = null;
    }
}
