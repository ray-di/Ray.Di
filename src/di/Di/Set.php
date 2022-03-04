<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY), Qualifier]
final class Set
{
    /** @var ""|class-string */
    public $interface;

    /** @var string */
    public $name;

    /**
     * @param ""|class-string $interface
     */
    public function __construct(string $interface, string $name = '')
    {
        $this->interface = $interface;
        $this->name = $name;
    }
}
