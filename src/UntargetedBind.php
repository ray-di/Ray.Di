<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\Unbound;

final class UntargetedBind
{
    /**
     * @param Container         $container
     * @param \ReflectionMethod $method
     */
    public function __invoke(Container $container, \ReflectionMethod $method)
    {
        $parameters = $method->getParameters();
        foreach ($parameters as &$parameter) {
            $parameter = $this->addConcreteClass($container, $parameter);
        }
    }

    /**
     * @param Container            $container
     * @param \ReflectionParameter $parameter
     */
    private function addConcreteClass(Container $container, \ReflectionParameter $parameter)
    {
        $class = $this->getTypeHint($parameter);
        if (class_exists($class)) {
            $container->add(new Bind($container, $class));
        }
    }

    /**
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    private function getTypeHint(\ReflectionParameter $parameter)
    {
        if (defined('HHVM_VERSION')) {
            return $parameter->info['type_hint']; // @codeCoverageIgnore
        }
        $typeHintClass = $parameter->getClass();

        return $typeHintClass ? $typeHintClass->name : '';
    }
}
