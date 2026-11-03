<?php

declare(strict_types=1);

namespace Ray\Di;

use function gettype;
use function is_object;

final class Instance implements DependencyInterface, AcceptInterface
{
    /** @param mixed $value */
    public function __construct(public $value)
    {
    }

    public function __toString(): string
    {
        if (is_object($this->value)) {
            return '(object) ' . $this->value::class;
        }

        return '(' . gettype($this->value) . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function register(array &$container, Bind $bind): void
    {
        $index = (string) $bind;
        $container[$index] = $bind->getBound();
    }

    /**
     * {@inheritDoc}
     */
    public function inject(Container $container)
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function setScope($scope): void
    {
    }

    /** @inheritDoc */
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitInstance($this->value);
    }
}
