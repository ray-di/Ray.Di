<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class UntargetedBind
{
    public function __invoke(Container $container, \ReflectionMethod $method)
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $this->addConcreteClass($container, $parameter);
        }
    }

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
            /* @noinspection PhpUndefinedFieldInspection */
            return $parameter->info['type_hint']; // @codeCoverageIgnore
        }
        $typeHintClass = $parameter->getClass();

        return $typeHintClass ? $typeHintClass->name : '';
    }
}
