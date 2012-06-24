<?php

namespace Ray\Di\Mock;

use Ray\Di\LoggerInterface;
use Ray\Aop\Bind;

class TestLogger implements LoggerInterface
{
    public static $log = false;

    public function log($class, array $params, array $setter, $object, Bind $bind)
    {
        $construct = serialize($params);
        $setter = serialize($setter);
        $intercept = ($object instanceof Weaver) ? (string) $object->___getBind() : '[]';
        $log = "Injector class={$class} constructor={$construct} setter={$setter} intercepter={$intercept}";
        self::$log = $log;
    }
}
