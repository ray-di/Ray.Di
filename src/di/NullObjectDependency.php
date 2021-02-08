<?php

declare(strict_types=1);

namespace Ray\Di;

use Koriym\NullObject\NullObject;
use ReflectionClass;

/**
 * @codeCoverageIgnore
 */
final class NullObjectDependency implements DependencyInterface
{
    /** @var string */
    private $interface;

    /**
     * @param class-string $interface
     */
    public function __construct(string $interface)
    {
        $this->interface = $interface;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function inject(Container $container)
    {
        /** @var string $scriptDir */
        $scriptDir = $container->getInstance('', 'scriptDir');
        assert(interface_exists($this->interface));
        $class = (new NullObject($scriptDir))($this->interface);

        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
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
    }
}
