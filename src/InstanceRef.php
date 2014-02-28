<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class InstanceRef
{
    /**
     * @var string
     */
    public $refIndex;

    /**
     * @param string $refIndex
     */
    public function __construct($refIndex)
    {
        $this->refIndex = $refIndex;
    }
}
