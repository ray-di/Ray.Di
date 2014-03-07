<?php

namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;

class ConcreteClass3RequiresConcreteClass2
{
    public $object;

    /**
     * @Inject
     */
    public function injectDependencies( ConcreteClass2NoConstructor $class )
    {
        $this->object = $class;
    }

}
