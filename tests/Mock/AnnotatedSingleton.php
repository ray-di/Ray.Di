<?php
namespace Ray\Di\Mock;

use Ray\Di\Di\Scope;
use Ray\Di\Di\PostConstruct;

/**
 * @Scope("Singleton")
 */
class AnnotatedSingleton {

    public static $number = 0;

    /**
     * @PostConstruct
     */
    public function onInit()
    {
        self::$number++;
    }

} 