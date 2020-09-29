<?php

declare(strict_types=1);

/**
 * This file is part of the _package_ package
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
