<?php

declare(strict_types=1);

namespace Ray\Di;

use Koriym\NullObject\NullObject;

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
        unset($container);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(array &$container, Bind $bind)
    {
        $container[(string) $bind] = $bind->getBound();
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        unset($scope);
    }
}
