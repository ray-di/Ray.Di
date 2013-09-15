<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Scope Definition
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
