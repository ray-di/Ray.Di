<?php

declare(strict_types=1);

/**
 * This file is part of the _package_ package
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
