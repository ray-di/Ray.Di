<?php

declare(strict_types=1);

namespace Ray\Di;

final class InjectionPoints
{
    /**
     * Injection points
     *
     * @var array [method name, name binding, is optional point ?][]
     */
    private $points = [];

    public function __invoke(string $class) : SetterMethods
    {
        $points = [];
        foreach ($this->points as $point) {
            $points[] = $this->getSetterMethod($class, $point);
        }

        return new SetterMethods($points);
    }

    public function addMethod(string $method, string $name = Name::ANY) : self
    {
        $this->points[] = [$method, $name, false];

        return $this;
    }

    public function addOptionalMethod(string $method, string $name = Name::ANY) : self
    {
        $this->points[] = [$method, $name, true];

        return $this;
    }

    private function getSetterMethod(string $class, array $point) : SetterMethod
    {
        $setterMethod = new SetterMethod(new \ReflectionMethod($class, $point[0]), new Name($point[1]));
        if ($point[2]) {
            $setterMethod->setOptional();
        }

        return $setterMethod;
    }
}
