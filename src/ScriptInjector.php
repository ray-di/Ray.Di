<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Exception\NotCompiled;

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
     * @param string $scriptDir generated instance script folder path
     */
    public function __construct($scriptDir)
    {
        $this->scriptDir = $scriptDir;
        $this->prototype = function ($dependencyIndex, array $injectionPoint = []) {
            $this->ip = $injectionPoint;

            return $this->getScriptInstance($dependencyIndex);
        };
        $this->singleton = function ($dependencyIndex, array $injectionPoint = []) {
            if (isset($singletons[$dependencyIndex])) {
                return $singletons[$dependencyIndex];
            }
            $this->ip = $injectionPoint;
            $instance = $this->getScriptInstance($dependencyIndex);
            $singletons[$dependencyIndex] = $instance;

            return $instance;
        };
        $this->injectionPoint = function () {
            return new InjectionPoint(
                new \ReflectionParameter([$this->ip[0], $this->ip[1]], $this->ip[2]),
                new AnnotationReader
            );
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        return $this->getScriptInstance($interface . '-' . $name);
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
            throw new NotCompiled($file);
        }
        $prototype = $this->prototype;
        $singleton = $this->singleton;
        $injection_point = $this->injectionPoint;

        $instance = require $file;

        return $instance;
    }
}
