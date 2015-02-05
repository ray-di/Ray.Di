<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;

final class InjectionPoint implements InjectionPointInterface
{
    /**
     * @var \ReflectionParameter
     */
    private $parameter;

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(\ReflectionParameter $parameter, Reader $reader)
    {
        $this->parameter = $parameter;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->parameter->getDeclaringFunction();
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->parameter->getDeclaringClass();
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodAnnotation($annotation = null)
    {
        if ($annotation) {
            return $this->reader->getMethodAnnotation($this->getMethod(), $annotation);
        }

        return $this->reader->getMethodAnnotations($this->getMethod());
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAnnotation($annotation = null)
    {
        if ($annotation) {
            return $this->reader->getClassAnnotation($this->getClass(), $annotation);
        }

        return $this->reader->getClassAnnotations($this->getClass());
    }
}
