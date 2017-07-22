<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Exception\Unbound;

final class Arguments
{
    /**
     * @var Argument[]
     */
    private $arguments = [];

    public function __construct(\ReflectionMethod $method, Name $name)
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $this->arguments[] = new Argument($parameter, $name($parameter));
        }
    }

    /**
     * Return arguments
     *
     * @param Container $container
     *
     * @throws Exception\Unbound
     *
     * @return Argument[]
     */
    public function inject(Container $container) : array
    {
        $parameters = $this->arguments;
        foreach ($parameters as &$parameter) {
            $parameter = $this->getParameter($container, $parameter);
        }

        return $parameters;
    }

    /**
     * @throws Unbound
     *
     * @return mixed
     */
    private function getParameter(Container $container, Argument $argument)
    {
        $this->bindInjectionPoint($container, $argument);
        try {
            return $container->getDependency((string) $argument);
        } catch (Unbound $e) {
            list($hasDefaultValue, $defaultValue) = $this->getDefaultValue($argument);
            if ($hasDefaultValue) {
                return $defaultValue;
            }
            throw new Unbound($argument->getMeta(), 0, $e);
        }
    }

    /**
     * @param Argument $argument
     *
     * @return array [$hasDefaultValue, $defaultValue]
     */
    private function getDefaultValue(Argument $argument) : array
    {
        if ($argument->isDefaultAvailable()) {
            return [true, $argument->getDefaultValue()];
        }

        return [false, null];
    }

    private function bindInjectionPoint(Container $container, Argument $argument) : void
    {
        $isSelf = (string) $argument === 'Ray\Di\InjectionPointInterface-' . Name::ANY;
        if ($isSelf) {
            return;
        }
        (new Bind($container, 'Ray\Di\InjectionPointInterface'))->toInstance(new InjectionPoint($argument->get(), new AnnotationReader));
    }
}
