<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use function get_class;
use Ray\Di\Di\Named;
use Ray\Di\Di\Qualifier;
use ReflectionClass;
use ReflectionMethod;

final class NameKeyVarString
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function __invoke(ReflectionMethod $method) : ?string
    {
        $keyVal = [];
        $named = $this->reader->getMethodAnnotation($method, Named::class);
        if ($named instanceof Named) {
            $keyVal[] = $named->value;
        }
        $qualifierNamed = $this->getQualifierKeyVarString($method);
        if ($qualifierNamed) {
            $keyVal[] = $qualifierNamed;
        }

        return $keyVal !== [] ? implode(',', $keyVal) : null; // var1=qualifier1,va2=qualifier2
    }

    private function getQualifierKeyVarString(ReflectionMethod $method) : string
    {
        /** @var array<object> $annotations */
        $annotations = $this->reader->getMethodAnnotations($method);
        $names = [];
        foreach ($annotations as $annotation) {
            $qualifier = $this->reader->getClassAnnotation(new ReflectionClass($annotation), Qualifier::class);
            if ($qualifier instanceof Qualifier) {
                /** @var ?scalar $annotation->value */
                $value = $annotation->value ?? Name::ANY; // @phpstan-ignore-line
                $names[] = sprintf('%s=%s', (string) $value, get_class($annotation)); // @phpstan-ignore-line
            }
        }

        return implode(',', $names);
    }
}
