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
     * @param LogInterface $log
     *
     * @Inject
     */
    public function setLog(LogInterface $log)
    {
        $this->log = $log;
    }
}
