<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * @see     https://github.com/auraphp/Aura.Di
 */
namespace Ray\Di;

use ReflectionClass;
use ReflectionException;

/**
 * Retains and unifies class configurations.
 *
 * @package Ray.Di
 */
class ApcConfig extends Config
{
    /**
     * (non-PHPdoc)
     * @see Aura\Di\ConfigInterface::fetch()
     */
    public function fetch($class)
    {
        try {
            // autoload
            class_exists($class, true);
            $file = (new ReflectionClass($class))->getFileName();
        } catch (ReflectionException $e) {
            throw new Exception\Configuration("{$class} not exists.");
        }
        $key = '[ray-config]' . __CLASS__ . $file . hash('crc32b', serialize($this->setter));
        $config = apc_fetch($key, $success);
        $config = $config ? ($config) : parent::fetch($class);
        if ($success !== true) {
            apc_store($key, ($config));
        }

        return $config;
    }
}
