<?php

declare(strict_types=1);

namespace Ray\Compiler;

use LogicException;
use Ray\Aop\ReflectionMethod;
use Ray\Di\InjectionPointInterface;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;

use function assert;
use function file_exists;
use function file_get_contents;
use function is_bool;
use function sprintf;
use function str_replace;
use function unserialize;

final class InjectionPoint implements InjectionPointInterface
{
    /** @var ReflectionParameter */
    private $parameter;

    /** @var string */
    private $scriptDir;

    public function __construct(ReflectionParameter $parameter, string $scriptDir)
    {
        $this->parameter = $parameter;
        $this->scriptDir = $scriptDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): \ReflectionMethod
    {
        $reflectionMethod = $this->parameter->getDeclaringFunction();
        assert($reflectionMethod instanceof ReflectionMethod);

        return $reflectionMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): ReflectionClass
    {
        $class = $this->parameter->getDeclaringClass();
        if (! $class instanceof ReflectionClass) {
            throw new LogicException(); // @codeCoverageIgnore
        }

        return $class;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<(object|null)>
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function getQualifiers(): array
    {
        return [$this->getQualifier()];
    }

    /**
     * {@inheritdoc}
     *
     * @return object|null
     */
    public function getQualifier()
    {
        $class = $this->parameter->getDeclaringClass();
        if (! $class instanceof ReflectionClass) {
            throw new LogicException(); // @codeCoverageIgnore
        }

        $qualifierFile = sprintf(
            ScriptInjector::QUALIFIER,
            $this->scriptDir,
            str_replace('\\', '_', $class->name),
            $this->parameter->getDeclaringFunction()->name,
            $this->parameter->name
        );
        if (! file_exists($qualifierFile)) {
            return null;
        }

        $qualifier = file_get_contents($qualifierFile);
        if (is_bool($qualifier)) {
            throw new RuntimeException(); // @codeCoverageIgnore
        }

        return unserialize($qualifier, ['allowed_classes' => true]);
    }
}
