<?php

declare(strict_types=1);

namespace Ray\Di;

use LogicException;
use Ray\Aop\BindInterface;
use Ray\Aop\CompilerInterface;

use function method_exists;

/**
 * Collects AOP bindings for a dependency
 *
 * @psalm-import-type PointcutList from Types
 * @psalm-type AopBindings = array<string, list<string>>
 */
final class AopCollector implements CompilerInterface
{
    /** @var AopBindings */
    private array $bindings = [];

    /**
     * @param PointcutList $pointcuts
     *
     * @return AopBindings
     */
    public function collect(Dependency $dependency, array $pointcuts): array
    {
        $this->bindings = [];
        $dependency->weaveAspects($this, $pointcuts);

        return $this->bindings;
    }

    /**
     * {@inheritDoc}
     *
     * @return never
     */
    public function newInstance(string $class, array $args, BindInterface $bind)
    {
        throw new LogicException('AopCollector::newInstance() should never be called');
    }

    /**
     * {@inheritDoc}
     */
    public function compile(string $class, BindInterface $bind): string
    {
        $bindings = $bind->getBindings();
        if (! $bindings) {
            return $class;
        }

        foreach ($bindings as $method => $interceptors) {
            if (! method_exists($class, $method)) {
                continue;
            }

            /** @var list<string> $interceptors */
            $this->bindings[$method] = $interceptors;
        }

        return $class;
    }
}
