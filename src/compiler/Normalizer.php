<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use Ray\Compiler\Exception\InvalidInstance;
use Ray\Di\InjectorInterface;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function serialize;

/**
 * Value to code(Node) converter
 */
final class Normalizer
{
    /**
     * Normalizes a value: Converts nulls, booleans, integers,
     * floats, strings and arrays into their respective nodes
     *
     * @param mixed $value The value to normalize
     *
     * @return Expr The normalized value
     */
    public function __invoke($value): Expr
    {
        if ($value === null) {
            return new Expr\ConstFetch(
                new Node\Name('null')
            );
        }

        if (is_bool($value)) {
            return new Expr\ConstFetch(
                new Node\Name($value ? 'true' : 'false')
            );
        }

        return $this->getValueNode($value);
    }

    /**
     * Return array or object node
     *
     * @param array<mixed>|mixed|object $value
     *
     * @return Scalar\String_|Scalar\LNumber|Scalar\DNumber|Expr\Array_|Expr\FuncCall
     */
    private function getValueNode($value): Expr
    {
        if (is_string($value)) {
            return new Scalar\String_($value);
        }

        if (is_int($value)) {
            return new Scalar\LNumber($value);
        }

        if (is_float($value)) {
            return new Scalar\DNumber($value);
        }

        return $this->getValueNodeNoScalar($value);
    }

    /**
     * Return array or object node
     *
     * @param array<mixed>|mixed|object $value
     *
     * @return Expr\Array_|Expr\FuncCall
     */
    private function getValueNodeNoScalar($value): Expr
    {
        if (is_array($value)) {
            return $this->arrayValue($value);
        }

        if (is_object($value)) {
            return $this->normalizeObject($value);
        }

        throw new InvalidInstance();
    }

    /**
     * Return "unserialize($object)" node
     *
     * @param object $object
     */
    private function normalizeObject($object): Expr\FuncCall
    {
        if ($object instanceof InjectorInterface) {
            return new Expr\FuncCall(new Expr\Variable('injector'));
        }

        $serialize = new Scalar\String_(serialize($object));

        return new Expr\FuncCall(new Node\Name('unserialize'), [new Arg($serialize)]);
    }

    /**
     * Return array value node
     *
     * @param array<int, mixed> $value
     */
    private function arrayValue($value): Expr\Array_
    {
        $items = [];
        $lastKey = -1;
        foreach ($value as $itemKey => $itemValue) {
            // for consecutive, numeric keys don't generate keys
            if ($lastKey !== null && ++$lastKey === $itemKey) {
                $items[] = new Expr\ArrayItem(
                    $this->__invoke($itemValue)
                );
            } else {
                $lastKey = null;
                $items[] = new Expr\ArrayItem(
                    $this->__invoke($itemValue),
                    $this->__invoke($itemKey)
                );
            }
        }

        return new Expr\Array_($items);
    }
}
