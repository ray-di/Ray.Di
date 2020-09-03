<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Exception\Unbound;
use ReflectionMethod;

final class Arguments
{
    /**
     * @var Argument[]
     */
    private $arguments = [];

    public function __construct(ReflectionMethod $method, Name $name)
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
     * @return array<int, mixed>
     */
    public function inject(Container $container) : array
    {
        $parameters = [];
        foreach ($this->arguments as $parameter) {
            /** @psalm-suppress MixedAssignment */
            $parameters[] = $this->getParameter($container, $parameter);
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
            if ($argument->isDefaultAvailable()) {
                return $argument->getDefaultValue();
            }

            throw new Unbound($argument->getMeta(), 0, $e);
        }
    }

    private function bindInjectionPoint(Container $container, Argument $argument) : void
    {
        $isSelf = (string) $argument === InjectionPointInterface::class . '-' . Name::ANY;
        if ($isSelf) {
            return;
        }
        (new Bind($container, InjectionPointInterface::class))->toInstance(new InjectionPoint($argument->get(), new AnnotationReader));
    }
}
