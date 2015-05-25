<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class FakeUntargetChild
{
    public $val;

    public function __construct($val)
    {
        $this->val = $val;
    }
}
