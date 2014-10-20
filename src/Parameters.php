<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\Unbound;

final class Parameters
{
    /**
     * @var Parameter[]
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
            $this->parameters[] = new Parameter($parameter, $name($parameter));
        }
    }

    /**
     * @param Container $container
     *
     * @return Parameter[]
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
     * @param Parameter $parameter
     *
     * @return mixed
     * @throws Unbound
     */
    private function getParameter(Container $container, Parameter $parameter)
    {
        try {
            return $container->getDependency((string) $parameter);
        } catch (Unbound $e) {
            return $this->getDefaultValue($e, $parameter);
        }
    }

    /**
     * @param Unbound   $e
     * @param Parameter $parameter
     *
     * @return mixed
     * @throws Unbound
     */
    private function getDefaultValue(Unbound $e, Parameter $parameter)
    {
        if ($parameter->isDefaultAvailable()) {
            return $parameter->getDefaultValue();
        }
        throw $e;
    }
}
