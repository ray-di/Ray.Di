<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeAbstract;
use Ray\Di\Argument;
use Ray\Di\Container;
use Ray\Di\InjectorInterface;
use Ray\Di\Instance;
use Ray\Di\Name;
use Ray\Di\SetterMethod;

final class FactoryCode
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var null|InjectorInterface
     */
    private $injector;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var FunctionCode
     */
    private $functionCompiler;

    public function __construct(
        Container $container,
        Normalizer $normalizer,
        DependencyCode $compiler,
        InjectorInterface $injector = null
    ) {
        $this->container = $container;
        $this->normalizer = $normalizer;
        $this->injector = $injector;
        $this->nodeFactory = new NodeFactory($normalizer, $this, $injector);
        $this->functionCompiler = new FunctionCode($container, new PrivateProperty, $compiler);
    }

    /**
     * @param array<Argument>     $arguments
     * @param array<SetterMethod> $setterMethods
     *
     * @return array<Expr>
     */
    public function getFactoryCode(string $class, array $arguments, array $setterMethods, string $postConstruct) : array
    {
        $node = [];
        $instance = new Expr\Variable('instance');
        // constructor injection
        $constructorInjection = $this->getConstructorInjection($class, $arguments);
        $node[] = new Expr\Assign($instance, $constructorInjection);
        $setters = $this->nodeFactory->getSetterInjection($instance, $setterMethods);
        foreach ($setters as $setter) {
            $node[] = $setter;
        }
        if ($postConstruct) {
            $node[] = $this->nodeFactory->getPostConstruct($instance, $postConstruct);
        }

        return $node;
    }

    /**
     * Return method argument code
     *
     * @return Expr|Expr\FuncCall
     */
    public function getArgStmt(Argument $argument) : NodeAbstract
    {
        $dependencyIndex = (string) $argument;
        if ($dependencyIndex === 'Ray\Di\InjectionPointInterface-' . Name::ANY) {
            return $this->getInjectionPoint();
        }
        $hasDependency = isset($this->container->getContainer()[$dependencyIndex]);
        if (! $hasDependency) {
            return $this->nodeFactory->getNode($argument);
        }
        $dependency = $this->container->getContainer()[$dependencyIndex];
        if ($dependency instanceof Instance) {
            return ($this->normalizer)($dependency->value);
        }

        return ($this->functionCompiler)($argument, $dependency);
    }

    /**
     * @param array<Argument> $arguments
     */
    private function getConstructorInjection(string $class, array $arguments = []) : Expr\New_
    {
        $args = [];
        foreach ($arguments as $argument) {
            //            $argument = $argument->isDefaultAvailable() ? $argument->getDefaultValue() : $argument;
            $args[] = $this->getArgStmt($argument);
        }
        /** @var array<Node\Arg> $args */
        return new Expr\New_(new Node\Name\FullyQualified($class), $args);
    }

    /**
     * Return "$injection_point()"
     */
    private function getInjectionPoint() : Expr
    {
        return new Expr\FuncCall(new Expr\Variable('injection_point'));
    }
}
