<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\NotFound;

final class NewReflectionMethod
{
    /**
     * @throws \ReflectionException
     */
    public function __invoke(object $object, string $method) : \ReflectionMethod
    {
        if (! method_exists($object, $method)) {
            throw new NotFound(sprintf('%s::%s', get_class($object), $method));
        }

        return new \ReflectionMethod($object, $method);
    }
}
