<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * Scope
 *
 * @Annotation
 * @Target("CLASS")
 */
final class Scope implements Annotation
{
    /**
     * Singleton
     *
     * @var string
     */
    const SINGLETON = 'singleton';

    /**
     * Prototype
     *
     * @var string
     */
    const PROTOTYPE = 'prototype';

    /**
     * Object lifecycle
     *
     * @var string
     */
    public $value = self::PROTOTYPE;
}
