<?php

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

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        if (! is_null($name)) {
            $this->setName($name);
        }
    }

    /**
     * @param string $name
     */
    private function setName($name)
    {
        // annotation
        if (class_exists($name, false)) {
            $this->name = $name;

            return;
        }
        // single name
        // @Named(name)
        if ($name === Name::ANY || preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
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
    private function parseName($name)
    {
        $keyValues  = explode(',', $name);
        foreach ($keyValues as $keyValue) {
            $exploded = explode('=', $keyValue);
            if (isset($exploded[1])) {
                list($key, $value) = $exploded;
                $this->names[trim($key)] = trim($value);
            }
        }
    }


    /**
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    public function __invoke(\ReflectionParameter $parameter)
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
        if (isset($this->names[Name::ANY])) {
            return $this->names[Name::ANY];
        }

        // not matched
        return Name::ANY;
    }
}
