<?php
namespace Ray\Di\Mock;

use Ray\Di\ProviderInterface;

class ConcreteClassWithoutConstructorProvider implements ProviderInterface {

    public function get()
    {
        return new ConcreteClassWithoutConstructor( "configuration" );
    }

} 