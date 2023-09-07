<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Named;
use Ray\Di\Di\Qualifier;

use function get_class;
use function implode;
use function sprintf;

final class NameKeyVarString
{
    public function __invoke(ReflectionMethod $method): ?string
    {
        $keyVal = [];
        $named = $method->getAnnotation(Named::class);
        if ($named instanceof Named) {
            $keyVal[] = $named->value;
        }

        $qualifierNamed = $this->getQualifierKeyVarString($method);
        if ($qualifierNamed) {
            $keyVal[] = $qualifierNamed;
        }

        return $keyVal !== [] ? implode(',', $keyVal) : null; // var1=qualifier1,va2=qualifier2
    }

    private function getQualifierKeyVarString(ReflectionMethod $method): string
    {
        $annotations = $method->getAnnotations();
        $names = [];
        foreach ($annotations as $annotation) {
            $qualifier = (new ReflectionClass($annotation))->getAnnotation(Qualifier::class);
            if ($qualifier instanceof Qualifier) {
                /** @var ?scalar $annotation->value */
                $value = $annotation->value ?? Name::ANY; // @phpstan-ignore-line
                $names[] = sprintf('%s=%s', (string) $value, get_class($annotation)); // @phpstan-ignore-line
            }
        }

        return implode(',', $names);
    }
}
