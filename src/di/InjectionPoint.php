<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Qualifier;
use ReflectionClass as CoreReflectionClass;
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
    private ?ReflectionParameter $parameter = null;

    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
        $this->pFunction = $parameter->getDeclaringFunction()->name;
        $class = $parameter->getDeclaringClass();
        $this->pClass = $class instanceof CoreReflectionClass ? $class->name : '';
        $this->pName = $parameter->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter(): ReflectionParameter
    {
        return $this->parameter ??= new ReflectionParameter([$this->pClass, $this->pFunction], $this->pName);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): ReflectionMethod
    {
        $parameter = $this->getParameter();
        $class = $parameter->getDeclaringClass();
        $method = $parameter->getDeclaringFunction()->getShortName();
        assert($class instanceof CoreReflectionClass);
        assert(class_exists($class->getName()));

        return new ReflectionMethod($class->getName(), $method);
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): ReflectionClass
    {
        $class = $this->getParameter()->getDeclaringClass();
        assert($class instanceof CoreReflectionClass);

        return new ReflectionClass($class->getName());
    }

    /**
     * {@inheritDoc}
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

    /** @return array<string> */
    public function __serialize(): array
    {
        return [$this->pClass, $this->pFunction, $this->pName];
    }

    /**
     * Not rebuilt here: doing so would force-autoload the declaring class
     * for every injection point in the graph regardless of use;
     * getParameter() rebuilds it lazily on first access.
     *
     * @param array<string> $array
     */
    public function __unserialize(array $array): void
    {
        [$this->pClass, $this->pFunction, $this->pName] = $array;
        $this->parameter = null;
    }
}
