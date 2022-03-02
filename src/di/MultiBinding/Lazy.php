<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\InjectorInterface;

final class Lazy
{
    /** @var string */
    private $class;

    /**
     * @param class-string|'' $interface
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function __invoke(InjectorInterface $injector)
    {
        return $injector->getInstance($this->class);
    }
}
