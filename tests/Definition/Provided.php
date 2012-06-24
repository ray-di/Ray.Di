<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\ReaderInterface;

use Ray\Di\Di\Inject;

class Provided
{
    /**
     * @var ReaderInterface
     */
    public $reader;

    /**
     * @Inject
     *
     * @param ReaderInterface $log
     */
    public function setReader(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }
}
