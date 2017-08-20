<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class Name
{
    /**
     * 'Unnamed' name
     */
    const ANY = '';

    /**
     * @var string
     */
    private $name;

    /**
     * Named database
     *
     * [named => varName][]
     *
     * @var string[]
     */
    private $names;

    public function __construct(string $name = null)
    {
        if (! is_null($name)) {
            $this->setName($name);
        }
    }

    public function __invoke(\ReflectionParameter $parameter) : string
    {
        // single variable named binding
        if ($this->name) {
            return $this->name;
        }

        // multiple variable named binding
        if (isset($this->names[$parameter->name])) {
            return $this->names[$parameter->name];
        }

        // ANY match
        if (isset($this->names[self::ANY])) {
            return $this->names[self::ANY];
        }

        // not matched
        return self::ANY;
    }

    private function setName(string $name)
    {
        // annotation
        if (class_exists($name, false)) {
            $this->name = $name;

            return;
        }
        // single name
        // @Named(name)
        if ($name === self::ANY || preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            $this->name = $name;

            return;
        }
        // name list
        // @Named(varName1=name1, varName2=name2)]
        $this->parseName($name);
    }

    /**
     * @param string $name
     */
    private function parseName(string $name)
    {
        $keyValues = explode(',', $name);
        foreach ($keyValues as $keyValue) {
            $exploded = explode('=', $keyValue);
            if (isset($exploded[1])) {
                list($key, $value) = $exploded;
                $this->names[trim($key)] = trim($value);
            }
        }
    }
}
