<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\BindInterface;
use Ray\Aop\CompilerInterface;
use Ray\Di\Bindings\AopInfo;

use function array_keys;
use function method_exists;

/**
 * @codeCoverageIgnore
 */
final class SpyCompiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-suppress InvalidReturnType
     * @template T of object
     */
    public function newInstance(string $class, array $args, BindInterface $bind)
    {
        // never called  // @phpstan-ignore-line
    }

    /**
     * Return "logging" class name
     *
     * Dummy classes are used for logging and don't really exist.
     * So the code breaks the QA rules as shown below.
     * NOTE: psalm-suppress is acceptable here for dummy/logging infrastructure
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function compile(string $class, BindInterface $bind): string
    {
        if ($this->hasNoBinding($class, $bind)) {
            return $class;
        }

        $aopInfo = $this->getInterceptors($bind);
        return $class . (string) $aopInfo; // @phpstan-ignore-line
    }

    /**
     * @param class-string $class
     */
    private function hasNoBinding(string $class, BindInterface $bind): bool
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    /**
     * @param class-string $class
     */
    private function hasBoundMethod(string $class, BindInterface $bind): bool
    {
        $bindingMethods = array_keys($bind->getBindings());
        $hasMethod = false;
        foreach ($bindingMethods as $bindingMethod) {
            if (method_exists($class, $bindingMethod)) {
                $hasMethod = true;
            }
        }

        return $hasMethod;
    }

    public function getAopInfo(BindInterface $bind): AopInfo
    {
        $bindings = $bind->getBindings();
        return new AopInfo($bindings);
    }

    private function getInterceptors(BindInterface $bind): AopInfo
    {
        return $this->getAopInfo($bind);
    }
}
