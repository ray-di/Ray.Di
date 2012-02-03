<?php

namespace Ray\Di;

// use Ray\Di\Di\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 * @BindingAnnotation
 */
final class SalesTax
{
    public $value = 1.05;
}