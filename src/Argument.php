<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class Argument
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
     * @var
     */
    private $default;

    /**
     * @var string
     */
    private $meta;

    /**
     * @var \ReflectionParameter
     */
    private $reflection;

    public function __construct(\ReflectionParameter $parameter, string $name)
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->index;
    }

    /**
     * Return reflection
     */
    public function get() : \ReflectionParameter
    {
        return $this->reflection;
    }

    /**
     * @return bool
     */
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

    private function setDefaultValue(\ReflectionParameter $parameter)
    {
        if (! $this->isDefaultAvailable) {
            return;
        }
        try {
            $this->default = $parameter->getDefaultValue();
        } catch (\ReflectionException $e) {
            // probably it is internal class like \PDO
            $this->default = null;
        }
    }

    private function getType(\ReflectionParameter $parameter) : string
    {
        $type = $parameter->getType();
        if ($type instanceof \ReflectionType && in_array((string) $type, ['bool', 'int', 'string', 'array'], true)) {
            return '';
        }

        return (string) $type;
    }
}
