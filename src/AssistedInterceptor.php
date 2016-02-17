<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Assisted;

final class AssistedInterceptor implements MethodInterceptor
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    /**
     * Intercepts any method and injects instances of the missing arguments
     * when they are type hinted
     */
    public function invoke(MethodInvocation $invocation)
    {
        $method = $invocation->getMethod();
        $assisted = $method->getAnnotation('Ray\Di\Di\Assisted');
        /* @var \Ray\Di\Di\Assisted $assisted */
        $parameters = $method->getParameters();
        $arguments = $invocation->getArguments()->getArrayCopy();
        if ($assisted instanceof Assisted && $method instanceof ReflectionMethod) {
            $arguments = $this->injectAssistedParameters($method, $assisted, $parameters, $arguments);
        }
        $invocation->getArguments()->exchangeArray($arguments);

        return $invocation->proceed();
    }

    /**
     * @param ReflectionMethod     $method
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    private function getName(ReflectionMethod $method, \ReflectionParameter $parameter)
    {
        $named = $method->getAnnotation('Ray\Di\Di\Named');
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

    /**
     * @param ReflectionMethod       $method
     * @param Assisted               $assisted
     * @param \ReflectionParameter[] $parameters
     * @param array                  $arguments
     *
     * @return array
     * @internal param int $cntArgs
     *
     */
    public function injectAssistedParameters(ReflectionMethod $method, Assisted $assisted, array $parameters, array $arguments)
    {
        foreach ($parameters as $pos => $parameter) {
            if (! in_array($parameter->getName(), $assisted->values)) {
                continue;
            }
            $hint = $parameter->getClass();
            $interface = $hint ? $hint->getName() : '';
            $name = $this->getName($method, $parameter);
            $arguments[] = $this->injector->getInstance($interface, $name);
        }

        return $arguments;
    }
}
