<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use ArrayAccess;
use Countable;
use Generator;
use Iterator;
use IteratorAggregate;
use LogicException;
use Ray\Di\InjectorInterface;
use ReturnTypeWillChange;

use function array_key_exists;
use function assert;
use function count;
use function is_object;

final class Map implements IteratorAggregate, ArrayAccess, Countable
{
    /** @var array<string, Lazy> $lazies */
    private $lazies;

    /** @var InjectorInterface */
    private $injector;

    /** @param array<string, Lazy> $lazies */
    public function __construct(array $lazies, InjectorInterface $injector)
    {
        $this->lazies = $lazies;
        $this->injector = $injector;
    }

    /** @param string $offset */
    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->lazies);
    }

    /**
     * @param string $offset
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $lazy = $this->lazies[$offset];

        /** @var Lazy $lazy */
        return $lazy($this->injector);
    }

    /**
     * @param string $offset
     * @param mixed  $value
     *
     * @return never
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        unset($offset, $value);

        throw new LogicException();
    }

    /**
     * @param string $offset
     *
     * @return never
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($offset);

        throw new LogicException();
    }

    /** @return Generator<int, object> */
    public function getIterator(): Iterator
    {
        foreach ($this->lazies as $lazy) {
            $object = ($lazy)($this->injector);
            assert(is_object($object));

            yield $object;
        }
    }

    public function count(): int
    {
        return count($this->lazies);
    }
}
