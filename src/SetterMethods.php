<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class SetterMethods
{
    /**
     * @var SetterMethod[]
     */
    private $setterMethods;

    public function __construct(array $setterMethods)
    {
        $this->setterMethods = $setterMethods;
    }

    /**
     * @param SetterMethod $setterMethod
     */
    public function add(SetterMethod $setterMethod = null)
    {
        if ($setterMethod) {
            $this->setterMethods[] = $setterMethod;
        }
    }

    /**
     * @param object    $instance
     * @param Container $container
     *
     * @throws Exception\Unbound
     * @throws \Exception
     */
    public function __invoke($instance, Container $container)
    {
        foreach ($this->setterMethods as $setterMethod) {
            /* @var SetterMethod $setterMethod */
            $setterMethod->__invoke($instance, $container);
        }
    }
}
