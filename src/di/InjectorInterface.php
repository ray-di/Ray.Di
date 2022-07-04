<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * Builds the graphs of objects that make up your application
 *
 * The injector tracks the dependencies for each type and uses bindings to inject them.
 * This is the core of Ray.Di, although you rarely interact with it directly.
 * This "behind-the-scenes" operation is what distinguishes dependency injection from its cousin, the service locator pattern.
 */
interface InjectorInterface
{
    /**
     * Return object graph
     *
     * @param ''|class-string<T> $interface
     * @param string             $name
     *
     * @return ($interface is '' ? mixed : T)
     *
     * @template T of object
     */
    public function getInstance($interface, $name = Name::ANY);
}
