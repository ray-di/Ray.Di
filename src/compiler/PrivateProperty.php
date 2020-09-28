<?php

declare(strict_types=1);

namespace Ray\Compiler;

final class PrivateProperty
{
    /**
     * @param null|object $object
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
            $refProp = (new \ReflectionProperty($object, $prop));
        } catch (\Exception $e) {
            return $default;
        }
        $refProp->setAccessible(true);

        return $refProp->getValue($object);
    }
}
