<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Qualifier;

final class InjectionPoint implements InjectionPointInterface, \Serializable
{
    /**
     * @var ?\ReflectionParameter
     *
     * this may lost on wakeUp
     */
    private $parameter;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $pClass;

    /**
     * @var string
     */
    private $pFunction;

    /**
     * @var string
     */
    private $pName;

    public function __construct(\ReflectionParameter $parameter, Reader $reader)
    {
        $this->parameter = $parameter;
        $this->pFunction = $parameter->getDeclaringFunction()->name;
        $class = $parameter->getDeclaringClass();
        $this->pClass = $class instanceof \ReflectionClass ? $class->name : '';
        $this->pName = $parameter->name;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter() : \ReflectionParameter
    {
        return $this->parameter ?? new \ReflectionParameter([$this->pClass, $this->pFunction], $this->pName);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod() : \ReflectionMethod
    {
        $this->parameter = $this->getParameter();
        $class = $this->parameter->getDeclaringClass();
        if (! $class instanceof \ReflectionClass) {
            throw new \LogicException($this->parameter->getName());
        }
        $method = $this->parameter->getDeclaringFunction()->getShortName();

        return new \ReflectionMethod($class->name, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function getClass() : \ReflectionClass
    {
        $this->parameter = $this->getParameter();
        $class = $this->parameter->getDeclaringClass();
        if ($class instanceof \ReflectionClass) {
            return $class;
        }

        throw new \LogicException($this->parameter->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getQualifiers() : array
    {
        $qualifiers = [];
        $annotations = $this->reader->getMethodAnnotations($this->getMethod());
        foreach ($annotations as $annotation) {
            $qualifier = $this->reader->getClassAnnotation(
                new \ReflectionClass($annotation),
                Qualifier::class
            );
            if ($qualifier instanceof Qualifier) {
                $qualifiers[] = $annotation;
            }
        }

        return $qualifiers;
    }

    public function serialize() : string
    {
        return serialize([$this->reader, $this->pClass, $this->pFunction, $this->pName]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized) : void
    {
        [$this->reader, $this->pClass, $this->pFunction, $this->pName] = unserialize($serialized, ['allowed_classes' => [AnnotationReader::class]]);
    }
}
