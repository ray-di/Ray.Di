<?php

declare(strict_types=1);

namespace Ray\Di\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Qualifier-only attribute with value property for testing multi-parameter resolution
 */
#[Attribute(Attribute::TARGET_METHOD), Qualifier]
final class FakeQualifierWithValue
{
    public function __construct(public string $value = '')
    {
    }
}
