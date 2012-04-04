<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Annotation scanner.
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
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
