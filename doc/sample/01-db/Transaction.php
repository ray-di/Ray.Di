<?php

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class Transaction implements MethodInterceptor
{
    /**
     * @param MethodInvocation $invocation
     *
     * @return mixed|void
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        $ref = new \ReflectionProperty($object, 'db');
        $ref->setAccessible(true);
        $db = $ref->getValue($object);
        $db->beginTransaction();
        try {
            echo "begin Transaction" . json_encode($invocation->getArguments()) . "\n";
            $invocation->proceed();
            $db->commit();
            echo "commit\n";
        } catch (\Exception $e) {
            $db->rollback();
        }
    }
}
