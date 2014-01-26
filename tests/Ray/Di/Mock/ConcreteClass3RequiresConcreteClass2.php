<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 26/01/14
 * Time: 16:28
 */

namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;

class ConcreteClass3RequiresConcreteClass2 {

    public $object;

    /**
     * @Inject
     */
    public function injectDependencies( ConcreteClass2NoConstructor $class )
    {
        $this->object = $class;
    }

} 