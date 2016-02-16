<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Aop\Annotation\AbstractAssisted;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

final class AssistedInterceptor implements MethodInterceptor
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(InjectorInterface $injector, Reader $reader)
    {
        $this->injector = $injector;
        $this->reader = $reader;
    }

    /**
     * Intercepts any method and injects instances of the missing arguments
     * when they are type hinted
     */
    public function invoke(MethodInvocation $invocation)
    {
        $method = $invocation->getMethod();
        $assisted = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\Assisted');
        /* @var \Ray\Di\Di\Assisted $assisted */
        $parameters = $method->getParameters();
        $arguments = $invocation->getArguments()->getArrayCopy();
        $cntArgs = count($arguments);
        foreach ($parameters as $pos => $parameter) {
            if ($pos < $cntArgs || ! $assisted || ! in_array($parameter->getName(), $assisted->values)) {
                continue;
            }
            $hint = $parameter->getClass();
            $interface = $hint ? $hint->getName() : '';
            $name = $this->getName($method, $parameter);
            $arguments[] = $this->injector->getInstance($interface, $name);
        }
        $invocation->getArguments()->exchangeArray($arguments);

        return $invocation->proceed();
    }

    private function getName(\ReflectionMethod $method, \ReflectionParameter $parameter)
    {
        $named = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\Named');
        if (! $named) {
            return Name::ANY;
        }
        parse_str($named->value, $names);
        $paramName = $parameter->getName();
        if (isset($names[$paramName])) {
            return $names[$paramName];
        }

        return Name::ANY;
    }
}