<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class Scope
{
    /**
     * Singleton scope
     *
     * @var string
     */
    const SINGLETON = 'Singleton';

    /**
     * Prototype scope
     *
     * @var string
     */
    const PROTOTYPE = 'Prototype';
}
