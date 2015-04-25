<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Exception\NotCompiled;
use Ray\Di\Exception\Unbound;

class ScriptInjector implements InjectorInterface
{
    /**
     * @var string
     */
    private $scriptDir;

    /**
     * Injection Point
     *
     * [$class, $method, $parameter]
     *
     * @var array
     */
    private $ip;

    /**
     * @var bool
     */
    private $isSingleton;

    /**
     * Singleton instance container
     *
     * @var array
     */
    private $singletons = [];

    /**
     * @param string $scriptDir generated instance script folder path
     */
    public function __construct($scriptDir)
    {
        $this->scriptDir = $scriptDir;
        $this->registerLoader();
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $dependencyIndex =  $interface . '-' . $name;
        if (isset($this->singletons[$dependencyIndex])) {
            return $this->singletons[$dependencyIndex];
        }
        $instance = $this->getScriptInstance($dependencyIndex);
        if ($this->isSingleton === true) {
            $this->singletons[$dependencyIndex] = $instance;
        }

        return $instance;
    }

    /**
     * @param string $dependencyIndex
     *
     * @return mixed
     */
    private function getScriptInstance($dependencyIndex)
    {
        $file = sprintf('%s/%s.php', $this->scriptDir, str_replace('\\', '_', $dependencyIndex));
        if (! file_exists($file)) {
            return $this->onDemandCompile($dependencyIndex);
        }
        $prototype = function ($dependencyIndex, array $injectionPoint = []) {
            $this->ip = $injectionPoint;

            return $this->getScriptInstance($dependencyIndex);
        };
        $singleton = function ($dependencyIndex, array $injectionPoint = []) {
            if (isset($this->singletons[$dependencyIndex])) {
                return $this->singletons[$dependencyIndex];
            }
            $this->ip = $injectionPoint;
            $instance = $this->getScriptInstance($dependencyIndex);
            $this->singletons[$dependencyIndex] = $instance;

            return $instance;
        };
        $injection_point = function () {
            return new InjectionPoint(
                new \ReflectionParameter([$this->ip[0], $this->ip[1]], $this->ip[2]),
                new AnnotationReader
            );
        };
        $injector = function () {
            return $this;
        };

        $instance = require $file;
        $this->isSingleton = $is_singleton;

        return $instance;
    }

    private function registerLoader()
    {
        spl_autoload_register(function ($class) {
            $file = sprintf('%s/%s.php', $this->scriptDir, $class);
            if (file_exists($file)) {
                // @codeCoverageIgnoreStart
                require $file;
                // @codeCoverageIgnoreEnd
            }
        });
    }

    /**
     * Return instance with compile on demand
     *
     * @param string $dependencyIndex
     *
     * @return mixed
     */
    private function onDemandCompile($dependencyIndex)
    {
        list($class, ) = explode('-', $dependencyIndex);
        $moduleFile = $this->scriptDir . '/module.php';
        if (! file_exists($moduleFile)) {
            throw new NotCompiled($class);
        }
        $module = require $moduleFile;
        $compiler = new DiCompiler($module, $this->scriptDir);
        try {
            return $compiler->getInstance($class);
        } catch (Unbound $e) {
            throw new NotCompiled($class, 500, $e);
        }
    }

    public function __wakeup()
    {
        $this->registerLoader();
    }
}
