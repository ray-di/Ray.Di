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
 * Annotation scanner.
 *
 * @package Aura.Di
 *
 */
interface AnnotationInterface
{
    /**
     * Get class definition by annotation
     *
     * @param string $class
     *
     * @return array
     */
    public function getDefinition($class);
}