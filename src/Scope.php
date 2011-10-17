<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Ray\Di;

/**
 * Scope Definition
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class Scope
{
    /**
     * Singleton scope
     *
     * @var string
     */
    const SINGLETON = 'singleton';

    /**
     * Prototype scope
     *
     * @var string
     */
    const PROTOTYPE = 'prototype';
}
