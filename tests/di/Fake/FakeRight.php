<?php
namespace Ray\Di;

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
class FakeRight
{
    public $value;
}
