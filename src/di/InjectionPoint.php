<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Qualifier;
use ReflectionParameter;

use function assert;
use function class_exists;

final class InjectionPoint implements InjectionPointInterface
{
    private string $pClass;

    /** @var string */
    private $pFunction;

    /** @var string */
    private $pName;

    public function __construct(private ReflectionParameter $parameter)
    {
        $this->pFunction = $this->parameter->getDeclaringFunction()->name;
        $class = $this->parameter->getDeclaringClass();
        $this->pClass = $class instanceof ReflectionClass ? $class->name : '';
        $this->pName = $this->parameter->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): ReflectionMethod
    {
        $class = $this->parameter->getDeclaringClass();
        $method = $this->parameter->getDeclaringFunction()->getShortName();
        assert($class instanceof \ReflectionClass);
        assert(class_exists($class->getName()));

        return new ReflectionMethod($class->getName(), $method);
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): ReflectionClass
    {
        $class = $this->parameter->getDeclaringClass();
        assert($class instanceof \ReflectionClass);

        return new ReflectionClass($class->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getQualifiers(): array
    {
        $qualifiers = [];
        $annotations = $this->getMethod()->getAnnotations();
        foreach ($annotations as $annotation) {
            $maybeQualifier = (new ReflectionClass($annotation))->getAnnotation(Qualifier::class);
            if ($maybeQualifier instanceof Qualifier) {
                $qualifiers[] = $annotation;
            }
        }

        return $qualifiers;
    }

    /**
     * @return array<string>
     */
    public function __serialize(): array
    {
        return [$this->pClass, $this->pFunction, $this->pName];
    }

    /**
     * @param array<string> $array
     */
    public function __unserialize(array $array): void
    {
        [$this->pClass, $this->pFunction, $this->pName] = $array;
        if ($this->pClass !== '') {
            $this->parameter = new ReflectionParameter([$this->pClass, $this->pFunction], $this->pName);
        }
    }
}
