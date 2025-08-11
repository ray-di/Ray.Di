<?php

declare(strict_types=1);

namespace Ray\Di\Bindings;

/**
 * Value object representing AOP binding information
 */
final class AopInfo
{
    /** @var array<string, list<string>> */
    public $methodBindings;

    /**
     * @param array<string, list<string>|list<object>> $methodBindings
     */
    public function __construct(array $methodBindings)
    {
        // Convert objects to string representations if needed
        $stringBindings = [];
        foreach ($methodBindings as $method => $interceptors) {
            $stringBindings[$method] = array_map('strval', $interceptors);
        }
        $this->methodBindings = $stringBindings;
    }

    /**
     * Check if any AOP bindings exist
     */
    public function hasBindings(): bool
    {
        return !empty($this->methodBindings);
    }

    /**
     * Convert to string representation for compatibility
     */
    public function __toString(): string
    {
        if (!$this->hasBindings()) {
            return '';
        }

        $log = ' (aop)';
        foreach ($this->methodBindings as $method => $interceptors) {
            $log .= \sprintf(
                ' +%s(%s)',
                $method,
                \implode(', ', $interceptors)
            );
        }

        return $log;
    }
}