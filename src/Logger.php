<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;

/**
 * Dependency injection loggers
 */
class Logger implements LoggerInterface, \IteratorAggregate, \Serializable
{
    /**
     * @var array
     */
    private $logMessages = [];

    /**
     * @var array [
     */
    private $logs = []; // [$class, array $params, array $setter, $object, Bind $bind]

    /**
     * @var array
     */
    private $hashes = [];

    /**
     * logger injection information
     *
     * @param BoundDefinition $definition
     * @param array           $params
     * @param array           $setter
     * @param object          $object
     * @param \Ray\Aop\Bind   $bind
     * @param bool            $isSingleton
     */
    public function log(BoundDefinition $definition, array $params, array $setter, $object, Bind $bind)
    {
        $this->logs[] = [$definition->class, $params, $setter, $object, $bind];
        $setterLog = [];
        foreach ($setter as $method => $methodParams) {
            $setterLog[] = $method . ':'. $this->getParamString((array) $methodParams);
        }
        $setter = $setter ? implode(' ', $setterLog) : '';
        $logMessage = "class:{$definition->class} $setter";
        $this->logMessages[] = $logMessage;
    }

    private function getParamString(array $params)
    {
        foreach ($params as &$param) {
            if (is_object($param)) {
                $param = get_class($param) . '#' . $this->getScope($param);
            } elseif (is_callable($param)) {
                $param = "(callable) {$param}";
            } elseif (is_scalar($param)) {
                $param = '(' . gettype($param) . ') ' . (string) $param;
            } elseif (is_array($param)) {
                $param = str_replace(["\n", " "], '', print_r($param, true));
            }
        }

        return implode(', ', $params);
    }

    private function getScope($object)
    {
        $hash = spl_object_hash($object);
        if (in_array($hash, $this->hashes)) {
            return 'singleton';
        }
        $this->hashes[] = $hash;

        return 'prototype';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(PHP_EOL, $this->logMessages);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->logs);
    }

    public function serialize()
    {
        return '';
    }

    public function unserialize($serialized)
    {
        unset($serialized);

        return '';
    }
}
