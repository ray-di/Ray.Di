<?php

declare(strict_types=1);

namespace Ray\Di;

use Koriym\NullObject\NullObject;
use Ray\Di\Annotation\ScriptDir;
use ReflectionClass;

use function assert;
use function class_exists;
use function interface_exists;
use function is_string;

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
        $scriptDir = $container->getInstance('', ScriptDir::class);
        assert(is_string($scriptDir));
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

    public function toNull(): Dependency
    {
        $nullObjectClass = $this->interface . 'Null';
        assert(class_exists($nullObjectClass));
        $class = new ReflectionClass($nullObjectClass);

        return new Dependency(new NewInstance($class, new SetterMethods([])));
    }
}
