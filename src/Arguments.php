<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\Unbound;

final class Arguments
{
    /**
     * @var Argument[]
     */
    private $parameters = [];

    /**
     * @param \ReflectionMethod $method
     * @param Name              $name
     */
    public function __construct(\ReflectionMethod $method, Name $name)
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $this->parameters[] = new Argument($parameter, $name($parameter));
        }
    }

    /**
     * @param Container $container
     *
     * @return Argument[]
     * @throws Exception\Unbound
     */
    public function get(Container $container)
    {
        $parameters = $this->parameters;
        foreach ($parameters as &$parameter) {
            $parameter = $this->getParameter($container, $parameter);
        }

        return $parameters;
    }

    /**
     * @param Container $container
     * @param Argument $parameter
     *
     * @return mixed
     * @throws Unbound
     */
    private function getParameter(Container $container, Argument $parameter)
    {
        list($class,) = explode('-', (string) $parameter);
        if (class_exists($class)) {
            // for aop
            $container->add((new Bind($container, $class))->to($class));
        }
        try {
            return $container->getDependency((string) $parameter);
        } catch (Unbound $e) {
            return $this->getDefaultValue($e, $parameter);
        }
    }

    /**
     * @param Unbound   $e
     * @param Argument $parameter
     *
     * @return mixed
     * @throws Unbound
     */
    private function getDefaultValue(Unbound $e, Argument $parameter)
    {
        if ($parameter->isDefaultAvailable()) {
            return $parameter->getDefaultValue();
        }
        throw $e;
    }
}
