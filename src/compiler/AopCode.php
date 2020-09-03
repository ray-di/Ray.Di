<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Scalar;
use Ray\Di\Dependency;
use Ray\Di\Name;

final class AopCode
{
    /**
     * @var PrivateProperty
     */
    private $privateProperty;

    public function __construct(PrivateProperty $privateProperty)
    {
        $this->privateProperty = $privateProperty;
    }

    /**
     * Add aop factory code if bindings are given
     *
     * @param array<Expr> $node
     *
     * @param-out array<Expr|mixed> $node
     */
    public function __invoke(Dependency $dependency, array &$node) : void
    {
        $prop = $this->privateProperty;
        $newInstance = $prop($dependency, 'newInstance');
        $bind = $prop($newInstance, 'bind');
        $bind = $prop($bind, 'bind');
        /** @var null|string[][] $bindings */
        $bindings = $prop($bind, 'bindings', null);
        if (! \is_array($bindings)) {
            return;
        }
        $methodBinding = $this->getMethodBinding($bindings);
        $bindingsProp = new Expr\PropertyFetch(new Expr\Variable('instance'), 'bindings');
        $bindingsAssign = new Assign($bindingsProp, new Expr\Array_($methodBinding));
        $this->setBindingAssignAfterInitialization($node, [$bindingsAssign], 1);
    }

    /**
     * @param array<Expr>   $array
     * @param array<Assign> $insertValue
     *
     * @param-out array<Expr|mixed> $array
     */
    private function setBindingAssignAfterInitialization(array &$array, array $insertValue, int $position) : void
    {
        $array = \array_merge(\array_splice($array, 0, $position), $insertValue, $array);
    }

    /**
     * @param string[][] $bindings
     *
     * @return Expr\ArrayItem[]
     */
    private function getMethodBinding(array $bindings) : array
    {
        $methodBinding = [];
        foreach ($bindings as $method => $interceptors) {
            $items = [];
            foreach ($interceptors as $interceptor) {
                // $singleton('FakeAopInterface-*');
                $dependencyIndex = "{$interceptor}-" . Name::ANY;
                $singleton = new Expr\FuncCall(new Expr\Variable('singleton'), [new Node\Arg(new Scalar\String_($dependencyIndex))]);
                // [$singleton('FakeAopInterface-*'), $singleton('FakeAopInterface-*');]
                $items[] = new Expr\ArrayItem($singleton);
            }
            $arr = new Expr\Array_($items);
            $methodBinding[] = new Expr\ArrayItem($arr, new Scalar\String_($method));
        }

        return $methodBinding;
    }
}
