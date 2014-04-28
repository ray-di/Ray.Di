<?php
namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;

class SingletonConsumer
{
    public static $instances = array();

    private $annotatedSingleton;

    /**
     * @param AnnotatedSingleton $annotatedSingleton
     *
     * @Inject
     */
    public function __construct( AnnotatedSingleton $annotatedSingleton )
    {
        $this->annotatedSingleton = $annotatedSingleton;
        self::$instances[] = $this;
    }

}
