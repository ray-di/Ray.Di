<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Aop\BindInterface;
use Ray\Aop\CompilerInterface;

final class SpyCompiler implements CompilerInterface
{
    /**
     * {@inheritdoc}
     */
    public function newInstance($class, array $args, BindInterface $bind)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function compile($class, BindInterface $bind) : string
    {
        if ($this->hasNoBinding($class, $bind)) {
            return $class;
        }
        $newClass = $class . $this->getInterceptors($bind);

        return $newClass;
    }

    private function hasNoBinding($class, BindInterface $bind) : bool
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    private function hasBoundMethod(string $class, BindInterface $bind) : bool
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

    private function getInterceptors(BindInterface $bind) : string
    {
        $log = '';
        foreach ($bind->getBindings() as $mehtod => $intepceptors) {
            $log .= sprintf(
                ' ::%s(%s)',
                $mehtod,
                implode(', ', $intepceptors)
            );
        }

        return $log;
    }
}
