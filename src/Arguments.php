<?php

declare(strict_types=1);

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
     * @return mixed
     * @throws Unbound
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
     * @return array [$hasDefaultValue, $defaultValue]
     */
    private function getDefaultValue(Argument $argument) : array
    {
        if ($argument->isDefaultAvailable()) {
            return [true, $argument->getDefaultValue()];
        }

        return [false, null];
    }

    private function bindInjectionPoint(Container $container, Argument $argument)
    {
        $isSelf = (string) $argument === InjectionPointInterface::class . '-' . Name::ANY;
        if ($isSelf) {
            return;
        }
        (new Bind($container, InjectionPointInterface::class))->toInstance(new InjectionPoint($argument->get(), new AnnotationReader));
    }
}
