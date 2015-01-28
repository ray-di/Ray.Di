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
    private $arguments = [];

    /**
     * @param \ReflectionMethod $method
     * @param Name              $name
     */
    public function __construct(\ReflectionMethod $method, Name $name)
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $this->arguments[] = new Argument($parameter, $name($parameter));
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
        $parameters = $this->arguments;
        foreach ($parameters as &$parameter) {
            $parameter = $this->getParameter($container, $parameter);
        }

        return $parameters;
    }

    /**
     * @param Container $container
     * @param Argument  $argument
     *
     * @return mixed
     * @throws Unbound
     */
    private function getParameter(Container $container, Argument $argument)
    {
        try {
            return $container->getDependency((string) $argument);
        } catch (Unbound $e) {
            return $this->getDefaultValue($e, $argument);
        }
    }

    /**
     * @param Unbound   $e
     * @param Argument  $argument
     *
     * @return mixed
     * @throws Unbound
     */
    private function getDefaultValue(Unbound $e, Argument $argument)
    {
        if ($argument->isDefaultAvailable()) {
            return $argument->getDefaultValue();
        }
        $message = sprintf("%s (%s)", $argument->getDebugInfo(), $argument);
        throw new Unbound($message  );
    }
}
