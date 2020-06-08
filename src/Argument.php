<?php

declare(strict_types=1);

namespace Ray\Di;

use function in_array;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Serializable;
use function serialize;

final class Argument implements Serializable
{
    /**
     * @var string
     */
    private $index;

    /**
     * @var bool
     */
    private $isDefaultAvailable;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var string
     */
    private $meta;

    /**
     * @var ReflectionParameter
     */
    private $reflection;

    public function __construct(ReflectionParameter $parameter, string $name)
    {
        $type = $this->getType($parameter);
        $isOptional = $parameter->isOptional();
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable() || $isOptional;
        if ($isOptional) {
            $this->default = null;
        }
        $this->setDefaultValue($parameter);
        $this->index = $type . '-' . $name;
        $this->reflection = $parameter;
        $this->meta = sprintf(
            "dependency '%s' with name '%s' used in %s:%d ($%s)",
            $type,
            $name,
            $this->reflection->getDeclaringFunction()->getFileName(),
            $this->reflection->getDeclaringFunction()->getStartLine(),
            $parameter->getName()
        );
    }

    public function __toString() : string
    {
        return $this->index;
    }

    /**
     * Return reflection
     */
    public function get() : ReflectionParameter
    {
        return $this->reflection;
    }

    public function isDefaultAvailable() : bool
    {
        return $this->isDefaultAvailable;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->default;
    }

    public function getMeta() : string
    {
        return $this->meta;
    }

    public function serialize() : string
    {
        /** @var ReflectionMethod $method */
        $method = $this->reflection->getDeclaringFunction();
        $ref = [
            $method->class,
            $method->name,
            $this->reflection->getName()
        ];

        return serialize([
            $this->index,
            $this->isDefaultAvailable,
            $this->default,
            $this->meta,
            $ref
        ]);
    }

    /**
     * @param string $serialized
     *
     * @throws ReflectionException
     */
    public function unserialize($serialized) : void
    {
        /** @var array{0: string, 1: bool, 2: string, 3: string, 4: string, 5: array{0: string, 1: string, 2:string}} $unserialized */
        $unserialized = unserialize($serialized, ['allowed_classes' => false]);
        [$this->index,
            $this->isDefaultAvailable,
            $this->default,
            $this->meta,
            $ref
        ] = $unserialized;
        $this->reflection = new ReflectionParameter([$ref[0], $ref[1]], $ref[2]);
    }

    private function setDefaultValue(ReflectionParameter $parameter) : void
    {
        if (! $this->isDefaultAvailable) {
            return;
        }
        try {
            $this->default = $parameter->getDefaultValue();
        } catch (ReflectionException $e) {
            // probably it is internal class like \PDO
            $this->default = null;
        }
    }

    private function getType(ReflectionParameter $parameter) : string
    {
        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType) {
            return '';
        }
        if (in_array($type->getName(), ['bool', 'int', 'string', 'array', 'resource', 'callable'], true)) {
            return '';
        }

        return $type->getName();
    }
}
