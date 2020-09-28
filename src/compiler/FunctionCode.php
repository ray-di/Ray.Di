<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use Ray\Di\Argument;
use Ray\Di\Container;
use Ray\Di\DependencyInterface;
use Ray\Di\DependencyProvider;
use Ray\Di\Di\Qualifier;

final class FunctionCode
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var PrivateProperty
     */
    private $privateProperty;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var DependencyCode
     */
    private $compiler;

    public function __construct(Container $container, PrivateProperty $privateProperty, DependencyCode $compiler)
    {
        $this->container = $container;
        $this->privateProperty = $privateProperty;
        $this->reader = new AnnotationReader;
        $this->compiler = $compiler;
    }

    /**
     * Return arguments code for "$singleton" and "$prototype"
     */
    public function __invoke(Argument $argument, DependencyInterface $dependency) : Expr\FuncCall
    {
        $prop = $this->privateProperty;
        $isSingleton = $prop($dependency, 'isSingleton');
        $func = $isSingleton ? 'singleton' : 'prototype';
        $args = $this->getInjectionFuncParams($argument);

        /** @var array<Node\Arg> $args */
        return new Expr\FuncCall(new Expr\Variable($func), $args);
    }

    /**
     * Return dependency index argument
     *
     * [class, method, param] is added if dependency is provider for DI context
     *
     * @return array<int, Node\Arg|Node\Expr\Array_>
     */
    private function getInjectionFuncParams(Argument $argument) : array
    {
        $dependencyIndex = (string) $argument;
        if ($this->container->getContainer()[$dependencyIndex] instanceof DependencyProvider) {
            return $this->getInjectionProviderParams($argument);
        }

        return [new Node\Arg(new Scalar\String_((string) $argument))];
    }

    /**
     * Return code for provider
     *
     * "$provider" needs [class, method, parameter] for InjectionPoint (Contextual Dependency Injection)
     *
     * @return array<int, Expr\Array_|Node\Arg>
     */
    private function getInjectionProviderParams(Argument $argument) : array
    {
        $param = $argument->get();
        $class = $param->getDeclaringClass();
        if (! $class instanceof \ReflectionClass) {
            throw new \LogicException; // @codeCoverageIgnore
        }
        $method = $param->getDeclaringFunction();
        assert($method instanceof \ReflectionMethod);
        $this->setQualifiers($method, $param);

        return [
            new Node\Arg(new Scalar\String_((string) $argument)),
            new Expr\Array_([
                new Expr\ArrayItem(new Scalar\String_($class->name)),
                new Expr\ArrayItem(new Scalar\String_($method->name)),
                new Expr\ArrayItem(new Scalar\String_($param->name))
            ])
        ];
    }

    private function setQualifiers(\ReflectionMethod $method, \ReflectionParameter $param) : void
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            $qualifier = $this->reader->getClassAnnotation(
                new \ReflectionClass($annotation),
                'Ray\Di\Di\Qualifier'
            );
            if ($qualifier instanceof Qualifier) {
                $this->compiler->setQaulifier(new IpQualifier($param, $annotation));
            }
        }
    }
}
