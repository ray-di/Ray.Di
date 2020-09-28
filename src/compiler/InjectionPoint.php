<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Aop\ReflectionMethod;
use Ray\Di\InjectionPointInterface;

final class InjectionPoint implements InjectionPointInterface
{
    /**
     * @var \ReflectionParameter
     */
    private $parameter;

    /**
     * @var string
     */
    private $scriptDir;

    public function __construct(\ReflectionParameter $parameter, string $scriptDir)
    {
        $this->parameter = $parameter;
        $this->scriptDir = $scriptDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter() : \ReflectionParameter
    {
        return $this->parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod() : \ReflectionMethod
    {
        $reflectionMethod = $this->parameter->getDeclaringFunction();
        assert($reflectionMethod instanceof ReflectionMethod);

        return $reflectionMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass() : \ReflectionClass
    {
        $class = $this->parameter->getDeclaringClass();
        if (! $class instanceof \ReflectionClass) {
            throw new \LogicException; // @codeCoverageIgnore
        }

        return $class;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<null|object>
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function getQualifiers() : array
    {
        return [$this->getQualifier()];
    }

    /**
     * {@inheritdoc}
     *
     * @return null|object
     */
    public function getQualifier()
    {
        $class = $this->parameter->getDeclaringClass();
        if (! $class instanceof \ReflectionClass) {
            throw new \LogicException; // @codeCoverageIgnore
        }
        $qualifierFile = \sprintf(
            ScriptInjector::QUALIFIER,
            $this->scriptDir,
            \str_replace('\\', '_', $class->name),
            $this->parameter->getDeclaringFunction()->name,
            $this->parameter->name
        );
        if (! \file_exists($qualifierFile)) {
            return null;
        }
        $qualifier = \file_get_contents($qualifierFile);
        if (\is_bool($qualifier)) {
            throw new \RuntimeException; // @codeCoverageIgnore
        }

        return \unserialize($qualifier, ['allowed_classes' => true]);
    }
}
