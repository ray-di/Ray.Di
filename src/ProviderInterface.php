<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

/**
 *
 * Interface for object provider.
 *
 * @package Aura.Di
 *
 */
interface ProviderInterface
{
    public function get();
}