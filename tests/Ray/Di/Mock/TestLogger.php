<?php

namespace Ray\Di\Mock;

use Ray\Di\LoggerInterface;
use Ray\Aop\Bind;
use Ray\Aop\Weaver;

class TestLogger implements LoggerInterface
{
    /**
     * @var bool
     */
    public static $log = false;

    /**
     * @param string $class
     * @param array  $params
     * @param array  $setter
     * @param object $object
     * @param Bind   $bind
     */
    public function log($class, array $params, array $setter, $object, Bind $bind)
    {
        $construct = serialize($params);
        $setter = serialize($setter);
        $intercept = ($object instanceof Weaver) ? (string)$object->___getBind() : '[]';
        $log = "Injector class={$class} constructor={$construct} setter={$setter} interceptor={$intercept}";
        self::$log = $log;
    }
}
