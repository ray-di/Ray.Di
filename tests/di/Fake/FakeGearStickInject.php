<?php

declare(strict_types=1);

namespace Ray\Di;

use Attribute;
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
#[Attribute]
class FakeGearStickInject implements InjectInterface
{
    public $value;

    public function isOptional()
    {
        return true;
    }

    public function __construct($value)
    {
        $this->value = $value['value'] ?? $value;
    }
}
