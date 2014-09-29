<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

interface BoundInstanceInterface
{
    /**
     * @param string         $class
     * @param AbstractModule $module
     *
     * @return bool
     */
    public function hasBound($class, AbstractModule $module, $name = AbstractModule::NAME_UNSPECIFIED);

    /**
     * @return object
     */
    public function getBound();

    /**
     * @return BoundDefinition
     */
    public function getDefinition();

    /**
     * @param string         $class
     * @param array          $params
     * @param AbstractModule $module
     *
     * @return mixed
     */
    public function bindConstruct($class, array $params, AbstractModule $module);
}
