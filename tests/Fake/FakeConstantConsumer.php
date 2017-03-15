<?php
namespace Ray\Di;

use Ray\Di\Di\Inject;

class FakeConstantConsumer
{
    public $constantByConstruct;

    public $constantBySetter;

    public $defaultByConstruct;

    public $defaultBySetter;

    public $setterConstantWithoutVarName;

    /**
     * @FakeConstant("constant")
     */
    public function __construct($constant, $default = 'default_construct')
    {
        $this->constantByConstruct = $constant;
        $this->defaultByConstruct = $default;
    }

    /**
     * @FakeConstant("constant")
     * @Inject
     */
    public function setFakeConstant($constant, $default = 'default_setter')
    {
        $this->constantBySetter = $constant;
        $this->defaultBySetter = $default;
    }

    /**
     * @FakeConstant
     * @Inject
     */
    public function setFakeConstantWithoutVarName($constant)
    {
        $this->setterConstantWithoutVarName = $constant;
    }
}
