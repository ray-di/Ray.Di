<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use ArrayObject;

final class LazyCollection extends ArrayObject
{
    /**
     * @param array<string, array<string, Lazy>> $array
     */
    public function __construct(array $array)
    {
        parent::__construct($array);
    }
}
