<?php

namespace Ray\Di;

use Aura\Di\ConfigInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Aop\Matcher;

final class InjectorFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig(ConfigInterface $config = null)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Return Injector instance
     *
     * @param array  $modules
     * @param Cache  $cache
     * @param string $tmpDir
     *
     * @return Injector
     */
    public function newInstance(
        array $modules = [],
        Cache $cache = null,
        $tmpDir = null
    ) {
        (new AopClassLoader)->register($tmpDir);

        $annotationReader = (new Locator)->getAnnotationReader();
        $config = $this->config ?: new Config(new Annotation(new Definition, $annotationReader));
        $logger = $this->logger ?: new Logger;
        $tmpDir =  $tmpDir ?: sys_get_temp_dir();
        $injector = new Injector(
            new Container(new Forge($config)),
            new EmptyModule,
            new Bind,
            new Compiler(
                $tmpDir,
                new PHPParser_PrettyPrinter_Default
            ),
            $logger
        );

        if (count($modules) > 0) {
            $module = array_shift($modules);
            if ($cache instanceof Cache && $module instanceof CacheableModule) {
                $module = $module->get($cache);
            }
            foreach ($modules as $extraModule) {
                /* @var $module AbstractModule */
                $module->install($extraModule);
            }
            $injector->setModule($module);
        }

        return $injector;
    }
}
