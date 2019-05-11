<?php

declare(strict_types=1);


namespace Ray\Di;

use Ray\Di\Exception\NotFound;

final class NewReflectionClass
{
    public function __invoke(string $class) : \ReflectionClass
    {
        if (! class_exists($class)) {
            throw new NotFound($class);
        }

        return new \ReflectionClass($class);
    }
}
