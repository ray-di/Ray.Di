<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\Inject;

class ArrayType
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     *
     * @Inject
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
