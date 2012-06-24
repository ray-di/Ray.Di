<?php

namespace Ray\Di\Tests;

/**
 * @Annotation
 * @Target("METHOD")
 * @BindingAnnotation
 */
final class SalesTax
{
    public $value = 1.05;
}
