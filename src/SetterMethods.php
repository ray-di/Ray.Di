<?php

declare(strict_types=1);

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
     * @throws Exception\Unbound
     * @throws \Exception
     */
    public function __invoke(object $instance, Container $container) : void
    {
        foreach ($this->setterMethods as $setterMethod) {
            /* @var SetterMethod $setterMethod */
            ($setterMethod)($instance, $container);
        }
    }

    /**
     * @param SetterMethod $setterMethod
     */
    public function add(SetterMethod $setterMethod = null) : void
    {
        if ($setterMethod) {
            $this->setterMethods[] = $setterMethod;
        }
    }
}
