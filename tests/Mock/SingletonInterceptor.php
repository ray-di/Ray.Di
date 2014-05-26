<?php

namespace Ray\Di\Mock;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;

class SingletonInterceptor implements MethodInterceptor
{
    /**
     * @param DbInterface $db
     *
     * @Inject
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->getThis()->setDb($this->db);
        return $invocation->proceed();
    }
}
