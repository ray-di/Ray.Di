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
 * Scope Definition
 *
 * @package Aura.Di
 *
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
