<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di\Di;

interface InjectInterface
{
    /** @noinspection ReturnTypeCanBeDeclaredInspection for BC */
    /**
     * Whether or not to use optional injection
     *
     * @return bool
     */
    public function isOptional();
}
