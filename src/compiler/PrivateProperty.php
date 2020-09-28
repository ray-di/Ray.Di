<?php

declare(strict_types=1);

namespace Ray\Compiler;

use ReflectionProperty;
use Throwable;

final class PrivateProperty
{
    /**
     * @param object|null $object
     * @param ?mixed      $default
     *
     * @return mixed
     */
    public function __invoke($object, string $prop, $default = null)
    {
        try {
            if ($object === null) {
                return $default;
            }

            $refProp = (new ReflectionProperty($object, $prop));
        } catch (Throwable $e) {
            return $default;
        }

        $refProp->setAccessible(true);

        return $refProp->getValue($object);
    }
}
