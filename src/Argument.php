<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class Argument
{
    /**
     * @var string
     */
    private $index;

    /**
     * @var bool
     */
    private $isDefaultAvailable;

    /**
     * @var
     */
    private $default;

    /**
     * @var string
     */
    private $meta;

    /**
     * @var \ReflectionParameter
     */
    private $reflection;

    public function __construct(\ReflectionParameter $parameter, $name)
    {
        $interface = $this->getTypeHint($parameter);
        $interface = ($interface === 'array') ? '' : $interface; // hhvm
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable();
        if ($this->isDefaultAvailable) {
            $this->default = $parameter->getDefaultValue();
        }
        $this->index = $interface . '-' . $name;
        $this->reflection = $parameter;
        $this->meta = sprintf(
            "dependency '%s' with name '%s' used in %s:%d",
            $interface,
            $name,
            $this->reflection->getDeclaringFunction()->getFileName(),
            $this->reflection->getDeclaringFunction()->getStartLine()
        );
    }

    /**
     * Return reflection
     *
     * @return \ReflectionParameter
     */
    public function get()
    {
        return $this->reflection;
    }

    /**
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    private function getTypeHint(\ReflectionParameter $parameter)
    {
        if (defined('HHVM_VERSION')) {
            /* @noinspection PhpUndefinedFieldInspection */
            return $parameter->info['type_hint']; // @codeCoverageIgnore
        }
        $typHint = $parameter->getClass();

        return $typHint instanceof \ReflectionClass ? $typHint->name : '';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function isDefaultAvailable()
    {
        return $this->isDefaultAvailable;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->default;
    }

    public function getMeta()
    {
        return $this->meta;
    }
}
