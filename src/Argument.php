<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
            /** @noinspection PhpUndefinedFieldInspection */
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

    /**
     * @return string
     */
    public function getDebugInfo()
    {
        return sprintf(
            "$%s in %s::%s()",
            $this->reflection->getName(),
            $this->reflection->getDeclaringClass()->getName(),
            $this->reflection->getDeclaringFunction()->getName()
        );
    }
}
