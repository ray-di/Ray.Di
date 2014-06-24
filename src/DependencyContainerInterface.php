<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

interface DependencyContainerInterface
{
    public function set($object);
    public function get($refId);
}
