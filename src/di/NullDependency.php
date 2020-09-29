<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * @codeCoverageIgnore
 */
final class NullDependency implements DependencyInterface
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function inject(Container $container)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(array &$container, Bind $bind)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
    }
}
