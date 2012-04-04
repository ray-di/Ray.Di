<?php
/**
 * Ray
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * Scope
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @package    Ray.Di
 * @subpackage Annotation
 */
final class Scope implements Annotation
{
    const SINGLETON = 'singleton';
    const PROTOTYPE = 'prototype';

    public $value = self::PROTOTYPE;
}
