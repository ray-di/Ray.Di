<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Annotation scanner.
 *
 * @package Ray.Di
 */
interface AnnotationInterface
{
    /**
     * Get class definition by annotation
     *
     * @param string $class
     *
     * @return Definition
     */
    public function getDefinition($class);
}
