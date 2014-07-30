<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class BoundDefinition
{
    /**
     * Class name
     *
     * @var string
     */
    public $class;

    /**
     * @var bool
     */
    public $isSingleton;

    /**
     * Interface name
     *
     * @var string
     */
    public $interfaceClass;

    /**
     * @var array
     */
    public $params;

    /**
     * Dependency setter
     *
     * @var array
     */
    public $setter;

    /**
     * Post construct method name
     *
     * @var string
     */
    public $postConstruct;

    /**
     * Pre destroy method name
     *
     * @var string
     */
    public $preDestroy;
}
