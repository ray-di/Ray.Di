<?php

namespace Ray\Di\Mock;

use Ray\Di\BoundDefinition;
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
     * @param BoundDefinition $definition
     * @param array           $params
     * @param array           $setter
     * @param object          $object
     * @param Bind            $bind
     * @param bool            $isSingleton
     */
    public function log(BoundDefinition $definition, array $params, array $setter, $object, Bind $bind)
    {
        $construct = serialize($params);
        $setter = serialize($setter);
        $intercept = ($object instanceof Weaver) ? (string) $object->___getBind() : '[]';
        $log = "Injector class={$definition->class} constructor={$construct} setter={$setter} interceptor={$intercept}";
        self::$log = $log;
    }
}
