<?php

declare(strict_types=1);

namespace Ray\Di;

use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Stringable;

use function assert;
use function in_array;
use function sprintf;

/**
 * @psalm-import-type DependencyIndex from Types
 * @psalm-import-type ArgumentSerializationData from Types
 */
final class Argument implements AcceptInterface, Stringable
{
    public const UNBOUND_TYPE = ['bool', 'int', 'float', 'string', 'array', 'resource', 'callable', 'iterable'];

    /** @var DependencyIndex */
    private string $index;
    private bool $isDefaultAvailable;

    /** @var mixed */
    private $default;
    private string $meta;
    private string $refClass;
    private string $refMethod;
    private string $refParam;
    private ?ReflectionParameter $reflection = null;

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
        $method = $parameter->getDeclaringFunction();
        assert($method instanceof ReflectionMethod);
        $this->refClass = $method->class;
        $this->refMethod = $method->name;
        $this->refParam = $parameter->getName();
        $this->meta = sprintf(
            "'%s-%s' in %s:%d ($%s)",
            $type,
            $name,
            $method->getFileName(),
            $method->getStartLine(),
            $parameter->getName()
        );
    }

    /**
     * Return index
     *
     * @return DependencyIndex
     */
    public function __toString(): string
    {
        return $this->index;
    }

    /**
     * Return reflection
     */
    public function get(): ReflectionParameter
    {
        return $this->reflection ??= new ReflectionParameter([$this->refClass, $this->refMethod], $this->refParam);
    }

    public function isDefaultAvailable(): bool
    {
        return $this->isDefaultAvailable;
    }

    /** @return mixed */
    public function getDefaultValue()
    {
        return $this->default;
    }

    public function getMeta(): string
    {
        return $this->meta;
    }

    /** @return ArgumentSerializationData */
    public function __serialize(): array
    {
        return [
            $this->index,
            $this->isDefaultAvailable,
            $this->default,
            $this->meta,
            [$this->refClass, $this->refMethod, $this->refParam],
        ];
    }

    /** @param ArgumentSerializationData $unserialized */
    public function __unserialize(array $unserialized): void
    {
        [
            $this->index,
            $this->isDefaultAvailable,
            $this->default,
            $this->meta,
            $ref,
        ] = $unserialized;
        [$this->refClass, $this->refMethod, $this->refParam] = $ref;
        $this->reflection = null;
    }

    /** @inheritDoc */
    public function accept(VisitorInterface $visitor): void
    {
        $visitor->visitArgument(
            $this->index,
            $this->isDefaultAvailable,
            $this->default,
            $this->get()
        );
    }

    private function setDefaultValue(ReflectionParameter $parameter): void
    {
        if (! $this->isDefaultAvailable) {
            return;
        }

        try {
            $this->default = $parameter->getDefaultValue();
            // @codeCoverageIgnoreStart
        } catch (ReflectionException) {
            $this->default = null;
            // @codeCoverageIgnoreEnd
        }
    }

    /** @psalm-pure */
    private function getType(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();

        return $type instanceof ReflectionNamedType && ! in_array($type->getName(), self::UNBOUND_TYPE, true) ? $type->getName() : '';
    }
}
