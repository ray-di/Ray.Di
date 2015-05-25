<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class FakeUntarget
{
    public $child;

    public function __construct(FakeUntargetChild $child)
    {
        $this->child = $child;
    }
}
