<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Return_;

final class DependencyCompiler
{
    /**
     * @var \PhpParser\BuilderFactory
     */
    private $factory;

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->factory = new \PhpParser\BuilderFactory;
        $this->container = $container;
    }

    /**
     * @param $dependencyIndex
     *
     * @return DependencyCompile
     */
    public function compileIndex($dependencyIndex)
    {
        $dependency = $this->container->getContainer()[$dependencyIndex];
        if ($dependency instanceof Dependency) {
            return $this->compileDependency($dependency);
        } elseif ($dependency instanceof Instance) {
            return $this->compileInstance($dependency);
        } elseif ($dependency instanceof DependencyProvider) {
            return $this->compileDependencyProvider($dependency);
        }

        throw new \LogicException($dependencyIndex);
    }

    /**
     * @param DependencyInterface $dependency
     *
     * @return DependencyCompile
     */
    public function compile(DependencyInterface $dependency)
    {
        if ($dependency instanceof Dependency) {
            return $this->compileDependency($dependency);
        } elseif ($dependency instanceof Instance) {
            return $this->compileInstance($dependency);
        }
    }

    private function compileInstance(Instance $instance)
    {
        $node = $this->normalizeValue($instance->value);
        return new DependencyCompile(new Return_($node));
    }

    private function compileDependency(Dependency $dependency)
    {
        $node = $this->getFactoryNode($dependency);
        $node[] =  new Return_(new Expr\Variable('instance'));
        $node = $this->factory->namespace('Ray\Di\Compiler')->addStmts($node)->getNode();

        return new DependencyCompile($node);
    }

    private function compileDependencyProvider(DependencyProvider $provider)
    {
        $dependency = $this->getPrivateProperty($provider, 'dependency');
        $node = $this->getFactoryNode($dependency);
        $node[] = new Return_(new Expr\MethodCall(new Expr\Variable('instance'), 'get'));
        $node = $this->factory->namespace('Ray\Di\Compiler')->addStmts($node)->getNode();

        return new DependencyCompile($node);
    }

//    private function getProviderNode($dependencyIndex, DependencyInterface $dependency)
//    {
//        $dependencyName = str_replace('-', '_', $dependencyIndex);
//        $providerName = "{$dependencyName}_Provider";
//
//        $node = $this->factory->namespace('Ray\Di\DependencyProvider')
//            ->addStmt($this->factory->class($providerName)
//            ->implement('Ray\Di\Provider')
//            ->makeFinal()
//                ->addStmt($this->factory->method('get')
//                    ->makePublic()
//                    ->addStmt($this->getFactory($dependency))
//                )
//            )
//            ->getNode();
//
//        return $node;
//    }

    private function getFactoryNode(DependencyInterface $dependency)
    {
        $newInstance = $this->getPrivateProperty($dependency, 'newInstance');
        // class name
        $class = $this->getPrivateProperty($newInstance, 'class');
        $setterMethods = (array) $this->getPrivateProperty($this->getPrivateProperty($newInstance, 'setterMethods'), 'setterMethods');
        $arguments = (array) $this->getPrivateProperty($this->getPrivateProperty($newInstance, 'arguments'), 'arguments');
        $postConstruct = $this->getPrivateProperty($dependency, 'postConstruct');
        $bind = $this->getPrivateProperty($newInstance, 'bind');

        return $this->getFactoryCode($class, $arguments, $setterMethods, $postConstruct);
    }

    /**
     * @param string $class
     * @param array  $arguments
     * @param array  $setterMethods
     * @param string $postConstruct
     *
     * @return Node[]
     */
    private function getFactoryCode($class, array $arguments, array $setterMethods, $postConstruct)
    {
        $node = [];
        $instance = new Expr\Variable('instance');
        // constructor injection
        $constructorInjection =  $this->constructorInjection($class, $arguments, $setterMethods);
        $node[] = new Expr\Assign($instance, $constructorInjection);
        $setters = $this->setterInjection($instance, $setterMethods);
        foreach ($setters as $setter) {
            $node[] = $setter;
        }
        if ($postConstruct) {
            $node[] = $this->postConstruct($instance, $postConstruct);
        }

        return $node;

    }

    private function constructorInjection($class, array $arguments = [])
    {
        /** @var $args Argument[] */
        $args = [];
        foreach ($arguments as $argument) {
            $args[] = $this->getArgStmt($argument);
        }
        $constructor = new Expr\New_(new FullyQualified($class), $args);

        return $constructor;
    }

    private function setterInjection(Expr\Variable $instance, array $setterMethods)
    {
        $setters = [];
        foreach($setterMethods as $setterMethod) {
            $method = $this->getPrivateProperty($setterMethod, 'method');
            $argumentsObject = $this->getPrivateProperty($setterMethod, 'arguments');
            $argumentsArray = $this->getPrivateProperty($argumentsObject, 'arguments');
            $args = [];
            foreach ($argumentsArray as $argument) {
                $args[] = $this->getArgStmt($argument);
            }

            $setters[] = new Expr\MethodCall($instance, $method, $args);
        }

        return $setters;
    }

    /**
     * @param Expr\Variable $instance
     * @param string        $postConstruct
     */
    private function postConstruct(Expr\Variable $instance, $postConstruct)
    {
        return new Expr\MethodCall($instance, $postConstruct);
    }

    private function getArgStmt(Argument $argument)
    {
        $dependencyIndex = (string) $argument;
        $dependency = $this->container->getContainer()[$dependencyIndex];
        if ($dependency instanceof Instance) {
            return $this->normalizeValue($dependency->value);
        }
        $isSingleton = $this->getPrivateProperty($dependency, 'isSingleton');
        $func = $isSingleton ? 'singleton' : 'prototype';
        $node = new Expr\FuncCall(new Expr\Variable($func), [new Arg(new Scalar\String((string) $argument))]);

        return $node;
    }

    private function getPrivateProperty($object, $prop, $default = null)
    {
        try {
            $refProp = (new \ReflectionProperty($object, $prop));
        } catch (\Exception $e) {
            return $default;
        }
        $refProp->setAccessible(true);
        $value = $refProp->getValue($object);

        return $value;
    }

    /**
     * Normalizes a value: Converts nulls, booleans, integers,
     * floats, strings and arrays into their respective nodes
     *
     * (taken from BuilderAbstract::PhpParser())
     *
     * @param mixed $value The value to normalize
     *
     * @return Expr The normalized value
     */
    protected function normalizeValue($value) {
        if ($value instanceof Node) {
            return $value;
        } elseif (is_null($value)) {
            return new Expr\ConstFetch(
                new Name('null')
            );
        } elseif (is_bool($value)) {
            return new Expr\ConstFetch(
                new Name($value ? 'true' : 'false')
            );
        } elseif (is_int($value)) {
            return new Scalar\LNumber($value);
        } elseif (is_float($value)) {
            return new Scalar\DNumber($value);
        } elseif (is_string($value)) {
            return new Scalar\String_($value);
        } elseif (is_array($value)) {
            $items = array();
            $lastKey = -1;
            foreach ($value as $itemKey => $itemValue) {
                // for consecutive, numeric keys don't generate keys
                if (null !== $lastKey && ++$lastKey === $itemKey) {
                    $items[] = new Expr\ArrayItem(
                        $this->normalizeValue($itemValue)
                    );
                } else {
                    $lastKey = null;
                    $items[] = new Expr\ArrayItem(
                        $this->normalizeValue($itemValue),
                        $this->normalizeValue($itemKey)
                    );
                }
            }

            return new Expr\Array_($items);
        } else {
            throw new \LogicException('Invalid value');
        }
    }
}
