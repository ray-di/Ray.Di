<?php

declare(strict_types=1);

namespace Ray\Di;

use Exception;

use function assert;

final class SetterMethods
{
    /** @var SetterMethod[] */
    private $setterMethods;

    /**
     * @param array<SetterMethod> $setterMethods
     */
    public function __construct(array $setterMethods)
    {
        $this->setterMethods = $setterMethods;
    }

    /**
     * @throws Exception
     */
    public function __invoke(object $instance, Container $container): void
    {
        foreach ($this->setterMethods as $setterMethod) {
            assert($setterMethod instanceof SetterMethod);
            ($setterMethod)($instance, $container);
        }
    }

    public function add(?SetterMethod $setterMethod = null): void
    {
        if ($setterMethod) {
            $this->setterMethods[] = $setterMethod;
        }
    }
}
