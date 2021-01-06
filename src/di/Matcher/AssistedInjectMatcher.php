<?php

declare(strict_types=1);

namespace Ray\Di\Matcher;

use LogicException;
use Ray\Aop\AbstractMatcher;
use Ray\Di\Di\Inject;
use Ray\Di\Di\InjectInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

final class AssistedInjectMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        throw new LogicException('Should not used in class matcher');
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        $params = $method->getParameters();
        foreach ($params as $param) {
            /** @var list<ReflectionAttribute> $attributes */
            $attributes = $param->getAttributes(InjectInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            if (isset($attributes[0])) {
                return true;
            }
        }

        return false;
    }
}
