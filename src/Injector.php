<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Ray\Aop\Compiler;
use Ray\Di\Exception\Untargetted;

class Injector implements InjectorInterface
{
    /**
     * @var string
     */
    private $classDir;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * @param AbstractModule $module
     * @param string         $classDir
     */
    public function __construct(AbstractModule $module = null, $classDir = null, CacheProvider $cache = null)
    {
        $this->classDir = $classDir ?: sys_get_temp_dir();
        $this->cache = clone ($cache ?: new ArrayCache);
        $module = $module ?: new EmptyModule;
        $this->cache->setNamespace(md5(serialize($module->getContainer())));
        $this->container = $module ? $module->getContainer() : new Container;
        $this->container->setDependencies($this->cache, $classDir);
        $this->container->weaveAspects(new Compiler($this->classDir));

        // builtin injection
        (new Bind($this->container, 'Ray\Di\InjectorInterface'))->toInstance($this);
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        try {
            $instance = $this->container->getInstance($interface, $name);
        } catch (Untargetted $e) {
            $this->container->bind($interface);
            $instance = $this->getInstance($interface, $name);
        }

        return $instance;
    }

    public function __wakeup()
    {
        spl_autoload_register(
            function ($class) {
                $file = $this->classDir . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($file)) {
                    // @codeCoverageIgnoreStart
                    /** @noinspection PhpIncludeInspection */
                    include $file;
                    // @@codeCoverageIgnoreEnd
                }
            }
        );
    }
}
