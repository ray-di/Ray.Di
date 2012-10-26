<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;

/**
 * Setter Injection
 *
 */
class ArrayType
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     *
     * Inject
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
