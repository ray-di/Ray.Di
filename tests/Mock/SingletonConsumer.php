<?php
namespace Ray\Di\Mock;

class SingletonConsumer {

    public static $instances = array();

    private $annotatedSingleton;

    public function __construct( AnnotatedSingleton $annotatedSingleton )
    {
        $this->annotatedSingleton = $annotatedSingleton;
        self::$instances[] = $this;
    }

} 