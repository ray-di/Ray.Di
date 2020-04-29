<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\BindInterface;
use Ray\Aop\CompilerInterface;

final class SpyCompiler implements CompilerInterface
{
    /**
     * {@inheritdoc}
     */
    public function newInstance(string $class, array $args, BindInterface $bind)
    {
        unset($class, $args, $bind);
        // never called
        return new \stdClass;
    }

    /**
     * Return "logging" class name
     *
     * Dummy classes are used for logging and don't really exist.
     * So the code breaks the QA rules as shown below.
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function compile(string $class, BindInterface $bind) : string
    {
        if ($this->hasNoBinding($class, $bind)) {
            return $class;
        }

        return $class . $this->getInterceptors($bind);
    }

    private function hasNoBinding(string $class, BindInterface $bind) : bool
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    private function hasBoundMethod(string $class, BindInterface $bind) : bool
    {
        $bindingMethods = array_keys($bind->getBindings());
        $hasMethod = false;
        foreach ($bindingMethods as $bindingMethod) {
            assert(class_exists($class));
            if (method_exists($class, $bindingMethod)) {
                $hasMethod = true;
            }
        }

        return $hasMethod;
    }

    private function getInterceptors(BindInterface $bind) : string
    {
        $bindings = $bind->getBindings();
        if (! $bindings) {
            return '';
        }
        $log = ' (aop)';
        foreach ($bindings as $mehtod => $intepceptors) {
            $log .= sprintf(
                ' +%s(%s)',
                $mehtod,
                implode(', ', $intepceptors)
            );
        }

        return $log;
    }
}
