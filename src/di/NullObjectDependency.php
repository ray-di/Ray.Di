<?php

declare(strict_types=1);

namespace Ray\Di;

use Koriym\NullObject\NullObject;
use Ray\Compiler\FilePutContents;
use Ray\Di\Annotation\ScriptDir;
use ReflectionClass;

use function assert;
use function class_exists;
use function interface_exists;
use function is_dir;
use function is_string;
use function sprintf;
use function str_replace;

/**
 * @codeCoverageIgnore
 */
final class NullObjectDependency implements DependencyInterface
{
    /** @var class-string */
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
        return null;
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

    public function toNull(string $scriptDir): Dependency
    {
        assert(is_dir($scriptDir));
        $nullObject = new NullObject($scriptDir);
        $code = $nullObject->getCode($this->interface);
        $nullObjectClass = $this->interface . 'Null';
        $file = sprintf('%s/%s.php', $scriptDir, str_replace('\\', '_', $nullObjectClass));
        (new FilePutContents())($file, $code);

        return new Dependency(new NewInstance(new ReflectionClass($nullObjectClass), new SetterMethods([])));
    }
}
