<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use Ray\Di\Container;
use Ray\Di\Dependency;
use Ray\Di\DependencyInterface;
use Ray\Di\DependencyProvider;
use Ray\Di\Instance;
use Ray\Di\SetContextInterface;

final class DependencyCode implements SetContextInterface
{
    /**
     * @var \PhpParser\BuilderFactory
     */
    private $factory;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var null|ScriptInjector
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

    /**
     * @var null|IpQualifier
     */
    private $qualifier;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $context;

    /**
     * @var AopCode
     */
    private $aopCode;

    public function __construct(Container $container, ScriptInjector $injector = null)
    {
        $this->factory = new BuilderFactory;
        $this->container = $container;
        $this->normalizer = new Normalizer;
        $this->injector = $injector;
        $this->factoryCompiler = new FactoryCode($container, new Normalizer, $this, $injector);
        $this->privateProperty = new PrivateProperty;
        $this->aopCode = new AopCode($this->privateProperty);
    }

    /**
     * Return compiled dependency code
     */
    public function getCode(DependencyInterface $dependency) : Code
    {
        if ($dependency instanceof Dependency) {
            return $this->getDependencyCode($dependency);
        }
        if ($dependency instanceof Instance) {
            return $this->getInstanceCode($dependency);
        }
        if ($dependency instanceof DependencyProvider) {
            return $this->getProviderCode($dependency);
        }

        throw new \DomainException(\get_class($dependency));
    }

    /**
     * {@inheritdoc}
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    public function setQaulifier(IpQualifier $qualifer) : void
    {
        $this->qualifier = $qualifer;
    }

    public function getIsSingletonCode(bool $isSingleton) : Expr\Assign
    {
        $bool = new Expr\ConstFetch(new Node\Name([$isSingleton ? 'true' : 'false']));

        return new Expr\Assign(new Expr\Variable('is_singleton'), $bool);
    }

    /**
     * Compile DependencyInstance
     */
    private function getInstanceCode(Instance $instance) : Code
    {
        $node = ($this->normalizer)($instance->value);

        return new Code(new Node\Stmt\Return_($node), false);
    }

    /**
     * Compile generic object dependency
     */
    private function getDependencyCode(Dependency $dependency) : Code
    {
        $prop = $this->privateProperty;
        $node = $this->getFactoryNode($dependency);
        ($this->aopCode)($dependency, $node);
        $isSingleton = $prop($dependency, 'isSingleton');
        $node[] = $this->getIsSingletonCode($isSingleton);
        $node[] = new Node\Stmt\Return_(new Node\Expr\Variable('instance'));
        /** @var Stmt\Namespace_ $namespace */
        $namespace = $this->factory->namespace('Ray\Di\Compiler')->addStmts($node)->getNode();
        $qualifer = $this->qualifier;
        $this->qualifier = null;

        return new Code($namespace, $isSingleton, $qualifer);
    }

    /**
     * Compile dependency provider
     */
    private function getProviderCode(DependencyProvider $provider) : Code
    {
        $prop = $this->privateProperty;
        $dependency = $prop($provider, 'dependency');
        $node = $this->getFactoryNode($dependency);
        $provider->setContext($this);
        if ($this->context) {
            $node[] = $this->getSetContextCode($this->context); // $instance->setContext($this->context);
        }
        $isSingleton = $prop($provider, 'isSingleton');
        $node[] = $this->getIsSingletonCode($isSingleton);
        $node[] = new Stmt\Return_(new MethodCall(new Expr\Variable('instance'), 'get'));
        $node = $this->factory->namespace('Ray\Di\Compiler')->addStmts($node)->getNode();
        $qualifer = $this->qualifier;
        $this->qualifier = null;

        return new Code($node, $isSingleton, $qualifer);
    }

    private function getSetContextCode(string $context) : MethodCall
    {
        $arg = new Node\Arg(new Node\Scalar\String_($context));

        return new MethodCall(new Expr\Variable('instance'), 'setContext', [$arg]);
    }

    /**
     * Return generic factory code
     *
     * This code is used by Dependency and DependencyProvider
     *
     * @return array<Expr>
     */
    private function getFactoryNode(DependencyInterface $dependency) : array
    {
        $prop = $this->privateProperty;
        $newInstance = $prop($dependency, 'newInstance');
        // class name
        $class = $prop($newInstance, 'class');
        $setterMethods = (array) $prop($prop($newInstance, 'setterMethods'), 'setterMethods');
        $arguments = (array) $prop($prop($newInstance, 'arguments'), 'arguments');
        $postConstruct = (string) $prop($dependency, 'postConstruct');

        return $this->factoryCompiler->getFactoryCode($class, $arguments, $setterMethods, $postConstruct);
    }
}
