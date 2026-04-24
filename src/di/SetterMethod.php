<?php

declare(strict_types=1);

namespace Ray\Di;

use Exception;
use Ray\Di\Exception\Unbound;
use ReflectionMethod;

final class SetterMethod implements AcceptInterface
{
    private readonly string $method;
    private readonly Arguments $arguments;

    /**
     * Is optional binding ?
     */
    private bool $isOptional = false;

    public function __construct(ReflectionMethod $method, Name $name)
    {
        $this->method = $method->name;
        $this->arguments = new Arguments($method, $name);
    }

    /**
     * @param object $instance
     *
     * @throws Unbound
     * @throws Exception
     */
    public function __invoke($instance, Container $container): void
    {
        try {
            $parameters = $this->arguments->inject($container);
        } catch (Unbound $unbound) {
            if ($this->isOptional) {
                return;
            }

            throw $unbound;
        }

        /** @psalm-suppress MixedMethodCall */
        $instance->{$this->method}(...$parameters);
    }

    public function setOptional(): void
    {
        $this->isOptional = true;
    }

    /** @inheritDoc */
    public function accept(VisitorInterface $visitor): void
    {
        try {
            $visitor->visitSetterMethod($this->method, $this->arguments);
        } catch (Unbound $unbound) {
            if ($this->isOptional) {
                // Return when no dependency given and @Inject(optional=true) annotated to setter method.
                return;
            }

            // Throw exception when no dependency given and @Inject(optional=false) annotated to setter method.
            throw $unbound;
        }
    }
}
