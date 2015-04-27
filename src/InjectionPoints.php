<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class InjectionPoints
{
    /**
     * Injection points
     *
     * @var array [method name, name binding, is optional point ?][]
     */
    private $points = [];

    /**
     * @param string $method setter method name
     * @param string $name   binding name
     *
     * @return $this
     */
    public function addMethod($method, $name = NAME::ANY)
    {
        $this->points[] = [$method, $name, false];

        return $this;
    }

    /**
     * @param string $method setter method name
     * @param string $name   binding name
     *
     * @return $this
     */
    public function addOptionalMethod($method, $name = NAME::ANY)
    {
        $this->points[] = [$method, $name, true];

        return $this;
    }

    /**
     * @param string $class
     *
     * @return SetterMethods
     */
    public function __invoke($class)
    {
        $points = [];
        foreach ($this->points as $point) {
            $points[] = $this->getSetterMethod($class, $point);
        }

        return new SetterMethods($points);
    }

    /**
     * @param string $class
     * @param array  $point
     *
     * @return SetterMethod
     */
    private function getSetterMethod($class, $point)
    {
        $setterMethod = new SetterMethod(new \ReflectionMethod($class, $point[0]), new Name($point[1]));
        if ($point[2]) {
            $setterMethod->setOptional();
        }

        return $setterMethod;
    }
}
