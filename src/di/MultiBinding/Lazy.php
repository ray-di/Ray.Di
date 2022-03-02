<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\InjectorInterface;

final class Lazy
{
    /** @var class-string */
    private $class;

    /**
     * @param class-string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function __invoke(InjectorInterface $injector)
    {
        return $injector->getInstance($this->class);
    }
}
