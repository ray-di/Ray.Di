<?php

namespace Ray\Di\Aop;

/**
 * @Annotation
 * @Target("METHOD")
 * @BindingAnnotation
 */
final class SalesTax
{
    public $value = 1.05;
}
