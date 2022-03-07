<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

use function assert;

use const PHP_VERSION_ID;

/**
 * Attribute/annotation reader for constructor parameters
 *
 * @template T of object
 */
class ParameterAttributeReader
{
    /** @var ?Reader */
    private $reader;

    /**
     * @Inject(optional=true)
     * @Named("annotation")
     */
    #[Inject(optional: true)]
    #[Named('annotation')]
    public function setReader(Reader $reader): void
    {
        $this->reader = $reader;
    }

    /**
     * Read the parameter attribute
     *
     * Attempts to read the attribute of the parameter,
     * and if not successful, attempts to read the annotation of the property of the same name
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function get(ReflectionParameter $param, string $class): ?object
    {
        if (PHP_VERSION_ID < 80000) {
            return $this->readAnnotation($param, $class);
        }

        /** @var array<ReflectionAttribute> $attributes */
        $attributes = $param->getAttributes($class);
        if ($attributes === []) {
            return $this->readAnnotation($param, $class);
        }

        $attribute = $attributes[0];
        /** @var T $instance */
        $instance = $attribute->newInstance();

        return $instance;
    }

    /**
     * @param class-string<T> $class
     *
     * @return T|null
     */
    private function readAnnotation(ReflectionParameter $param, string $class)
    {
        $reader = $this->reader ?? new AnnotationReader();
        $ref = $param->getDeclaringClass();
        assert($ref instanceof ReflectionClass);
        $prop = new ReflectionProperty($ref->getName(), $param->getName());

        return $reader->getPropertyAnnotation($prop, $class);
    }
}
