<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER), Qualifier]
final class Set
{
    /** @var string */
    public $interface;

    /** @var string */
    public $name;

    public function __construct(string $interface, string $name = '')
    {
        $this->interface = $interface;
        $this->name = $name;
    }
}
