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
        $isOptional = $parameter->isOptional();
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable() || $isOptional;
        if ($isOptional) {
            $this->default = null;
        }
        if ($this->isDefaultAvailable) {
            try {
                $this->default = $parameter->getDefaultValue();
            } catch (\ReflectionException $e) {
                // probably it is internal class like \PDO
                $this->default = null;
            }
        }
        $this->index = $interface . '-' . $name;
        $this->reflection = $parameter;
        $this->meta = sprintf(
            "dependency '%s' with name '%s' used in %s:%d ($%s)",
            $interface,
            $name,
            $this->reflection->getDeclaringFunction()->getFileName(),
            $this->reflection->getDeclaringFunction()->getStartLine(),
            $parameter->getName()
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
