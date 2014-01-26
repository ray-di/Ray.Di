<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 26/01/14
 * Time: 15:50
 */

namespace Ray\Di\Mock;

use Ray\Di\Di\Scope;

/**
 * @Scope("Singleton")
 */
abstract class AbstractClass2RequiresAbstractClassWithConstructor {

    public $object;

    public function __construct( AbstractClassWithConstructor $object )
    {
        $this->object = $object;
    }

} 