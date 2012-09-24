<?php
namespace Ray\Di\Definition;

use Ray\Di\Mock\logInterface;
use Ray\Di\Di\Inject;

class Implemented
{
    /**
     * @var LogInterface
     */
    public $log;

    /**
     * @Inject
     *
     * @param LogInterface $log
     */
    public function setLog(LogInterface $log)
    {
        $this->log = $log;
    }
}
