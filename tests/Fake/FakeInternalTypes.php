<?php
namespace Ray\Di;

use Ray\Di\Di\Named;

class FakeInternalTypes
{
    public $bool;
    public $int;
    public $string;
    public $array;
    public $callable;

    /**
     * @Named("bool=type-bool,int=type-int,string=type-string,array=type-array,callable=type-callable")
     */
    public function __construct(
        bool $bool,
        int $int,
        string $string,
        array $array,
        callable $callable
    ) {
        $this->bool = $bool;
        $this->int = $int;
        $this->string = $string;
        $this->array = $array;
        $this->callable = $callable;
    }

    public function stringId(string $id)
    {
        unset($id);
    }
}
