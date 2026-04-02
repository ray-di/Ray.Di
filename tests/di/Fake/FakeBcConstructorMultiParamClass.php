<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Annotation\FakeQualifierWithValue;

class FakeBcConstructorMultiParamClass
{
    /**
     * Constructor with multiple parameters and method-level Qualifier with value
     * BcParameterQualifier should apply to the parameter matching the value
     */
    #[FakeQualifierWithValue('param2')]
    public function __construct(public $param1, public $param2)
    {
    }
}
