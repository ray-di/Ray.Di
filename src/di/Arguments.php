<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\NoHint;
use Ray\Di\Exception\Unbound;
use ReflectionMethod;

use function sprintf;

/**
 * @psalm-import-type ArgumentsList from Types
 * @psalm-import-type MethodArguments from Types
 */
final class Arguments implements AcceptInterface
{
    /** @var ArgumentsList */
    private array $arguments = [];

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
     * @return list<mixed>
     *
     * @throws Exception\Unbound
     */
    public function inject(Container $container): array
    {
        $parameters = [];
        foreach ($this->arguments as $parameter) {
            /** @psalm-suppress MixedAssignment */
            $parameters[] = $this->getParameter($container, $parameter);
        }

        return $parameters;
    }

    public function accept(VisitorInterface $visitor): void
    {
        $visitor->visitArguments($this->arguments);
    }

    /**
     * @return mixed
     *
     * @throws Unbound
     */
    private function getParameter(Container $container, Argument $argument)
    {
        $isInjectionPointItself = (string) $argument === InjectionPointInterface::class . '-' . Name::ANY;
        $previousInjectionPoint = $isInjectionPointItself
            ? null
            : $container->setInjectionPoint(new InjectionPoint($argument->get()));
        try {
            return $container->getDependency((string) $argument);
        } catch (Unbound $unbound) {
            if ($argument->isDefaultAvailable()) {
                return $argument->getDefaultValue();
            }

            if ($unbound instanceof NoHint) {
                throw new NoHint($this->getNoHintMsg($argument), 0, $unbound);
            }

            throw new Unbound($argument->getMeta(), 0, $unbound);
        } finally {
            if (! $isInjectionPointItself) {
                $container->restoreInjectionPoint($previousInjectionPoint);
            }
        }
    }

    private function getNoHintMsg(Argument $argument): string
    {
        $ref = $argument->get();
        $func = $ref->getDeclaringFunction();
        $fileName = $func->getFileName();

        return sprintf(
            '$%s (%s:%d)',
            $ref->getName(),
            $fileName !== false ? $fileName : 'unknown file',
            $func->getStartLine()
        );
    }
}
