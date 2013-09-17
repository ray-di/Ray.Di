<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;

/**
 * Setter Injection
 *
 */
class UseBasic implements BasicInterface
{
    public $b0;
    public $b1;
    public $b2;

    /**
     * @Inject
     */
    public function __construct(BasicInterface $b)
    {
        $this->b0 = $b;
    }

    /**
     * @Inject
     */
    public function setBasic1(BasicInterface $b)
    {
        $this->b1 = $b;
    }

    /**
     * @Inject
     */
    public function setBasic2(BasicInterface $b1, BasicInterface $b2)
    {
    }
}
