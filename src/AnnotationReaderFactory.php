<?php
/**
 * This file is part of the {package} package
 *
 * @package {package}
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationReaderFactory
{
    private static $instance;

    public function setCache(Cache $cache)
    {
        self::$instance = new CachedReader(new AnnotationReader, $cache);
    }

    public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new AnnotationReader;
        }

        return self::$instance;
    }
}
