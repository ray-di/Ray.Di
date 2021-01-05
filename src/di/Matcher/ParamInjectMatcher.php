<?php

declare(strict_types=1);

namespace Ray\Di\Matcher;

use Ray\Aop\AbstractMatcher;
use Ray\Di\Di\Inject;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

final class ParamInjectMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        throw new \LogicException('Should not used in class matcher');
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        $params = $method->getParameters();
        foreach ($params as $param) {
            /** @var list<ReflectionAttribute> $attributes */
            $attributes = $param->getAttributes(Inject::class);
            if (isset($attributes[0])) {
                return true;
            }
        }

        return false;
    }
}
