<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use Ray\Compiler\Exception\NotCompiled;
use Ray\Di\Argument;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Ray\Di\SetterMethod;

final class NodeFactory
{
    /**
     * @var null|InjectorInterface
     */
    private $injector;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var FactoryCode
     */
    private $factoryCompiler;

    /**
     * @var PrivateProperty
     */
    private $privateProperty;

    public function __construct(
        Normalizer $normalizer,
        FactoryCode $factoryCompiler,
        InjectorInterface $injector = null
    ) {
        $this->injector = $injector;
        $this->normalizer = $normalizer;
        $this->factoryCompiler = $factoryCompiler;
        $this->privateProperty = new PrivateProperty;
    }

    /**
     * Return on-demand dependency pull code for not compiled
     *
     * @return Expr|Expr\FuncCall
     */
    public function getNode(Argument $argument) : Expr
    {
        $dependencyIndex = (string) $argument;
        if (! $this->injector instanceof ScriptInjector) {
            return $this->getDefault($argument);
        }
        try {
            $isSingleton = $this->injector->isSingleton($dependencyIndex);
        } catch (NotCompiled $e) {
            return $this->getDefault($argument);
        }
        $func = $isSingleton ? 'singleton' : 'prototype';
        $args = $this->getInjectionProviderParams($argument);

        /** @var array<Node\Arg> $args */
        return new Expr\FuncCall(new Expr\Variable($func), $args);
    }

    /**
     * @param SetterMethod[] $setterMethods
     *
     * @return Expr\MethodCall[]
     */
    public function getSetterInjection(Expr\Variable $instance, array $setterMethods) : array
    {
        $setters = [];
        foreach ($setterMethods as $setterMethod) {
            $isOptional = ($this->privateProperty)($setterMethod, 'isOptional');
            $method = ($this->privateProperty)($setterMethod, 'method');
            $argumentsObject = ($this->privateProperty)($setterMethod, 'arguments');
            $arguments = ($this->privateProperty)($argumentsObject, 'arguments');
            $args = $this->getSetterParams($arguments, $isOptional);
            if (! $args) {
                continue;
            }
            /** @var array<Node\Arg> $args */
            $setters[] = new Expr\MethodCall($instance, $method, $args); // @phpstan-ignore-line
        }

        return $setters;
    }

    public function getPostConstruct(Expr\Variable $instance, string $postConstruct) : Expr\MethodCall
    {
        return new Expr\MethodCall($instance, $postConstruct);
    }

    /**
     * Return default argument value
     */
    private function getDefault(Argument $argument) : Expr
    {
        if ($argument->isDefaultAvailable()) {
            $default = $argument->getDefaultValue();

            return ($this->normalizer)($default);
        }

        throw new Unbound($argument->getMeta());
    }

    /**
     * Return code for provider
     *
     * "$provider" needs [class, method, parameter] for InjectionPoint (Contextual Dependency Injection)
     *
     * @return array<Expr\Array_|Node\Arg>
     */
    private function getInjectionProviderParams(Argument $argument)
    {
        $param = $argument->get();
        $class = $param->getDeclaringClass();
        if (! $class instanceof \ReflectionClass) {
            throw new \LogicException; // @codeCoverageIgnore
        }

        return [
            new Node\Arg(new Scalar\String_((string) $argument)),
            new Expr\Array_([
                new Node\Expr\ArrayItem(new Scalar\String_($class->name)),
                new Node\Expr\ArrayItem(new Scalar\String_($param->getDeclaringFunction()->name)),
                new Node\Expr\ArrayItem(new Scalar\String_($param->name))
            ])
        ];
    }

    /**
     * Return setter method parameters
     *
     * Return false when no dependency given and @ Inject(optional=true) annotated to setter method.
     *
     * @param Argument[] $arguments
     *
     * @return false|Node\Expr[]
     */
    private function getSetterParams(array $arguments, bool $isOptional)
    {
        $args = [];
        foreach ($arguments as $argument) {
            try {
                $args[] = $this->factoryCompiler->getArgStmt($argument);
            } catch (Unbound $e) {
                if ($isOptional) {
                    return false;
                }
            }
        }

        return $args;
    }
}
