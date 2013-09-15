<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;
use Ray\Di\LoggerInterface;

/**
 * Dependency injection logger.
 */
class Logger implements LoggerInterface
{
    /**
     * @var string
     */
    private $logMessages = [];

    /**
     * logger injection information
     *
     * @param string        $class
     * @param array         $params
     * @param array         $setter
     * @param object        $object
     * @param \Ray\Aop\Bind $bind
     */
    public function log($class, array $params, array $setter, $object, Bind $bind)
    {
        unset($object);
        unset($bind);
        $toStr = function ($params) {
            foreach ($params as &$param) {
                if (is_object($param)) {
                    $param = get_class($param) . '#' . spl_object_hash($param);
                } elseif (is_callable($param)) {
                    $param = "(callable) {$param}";
                } elseif (is_scalar($param)) {
                    $param = '(' . gettype($param) . ') ' . (string)$param;
                } elseif (is_array($param)) {
                    $param = str_replace(["\n", " "], '', print_r($param, true));
                }
            }
            return implode(', ', $params);
        };
        $constructor = $toStr($params);
        $constructor = $constructor ? $constructor : '';
        $setter = $setter ? "setter[" . implode(', ', array_keys($setter)) . ']' : '';
        $logMessage = "[DI] {$class} construct[$constructor] {$setter}";
        $this->logMessages[] = $logMessage;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(PHP_EOL, $this->logMessages);
    }
}
