<?php
namespace Ray\Di;

use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
class FakeGearStickInject implements InjectInterface
{
    public $value;

    public function isOptional()
    {
        return true;
    }
}
