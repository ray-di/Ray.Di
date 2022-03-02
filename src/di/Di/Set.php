<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;

use function sprintf;

#[Attribute(Attribute::TARGET_PARAMETER), Qualifier]
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

    public function getKey(): string
    {
        return sprintf('%s-%s', $this->interface, $this->name);
    }
}
