<?php

declare(strict_types=1);

namespace Ray\Di;

use DateTime;
use DateTimeInterface;

final class InjectorInterfaceTest
{
    private DateTimeInterface $a; // @phpstan-ignore-line
    private DateTime $b; // @phpstan-ignore-line
    private mixed $m; // @phpstan-ignore-line

    public function __construct()
    {
        $injector = new Injector(new class extends AbstractModule {
            protected function configure()
            {
            }
        });
        $injector->getInstance(DateTimeInterface::class);
        $this->a = $injector->getInstance(DateTimeInterface::class); // @phpstan-ignore-line
        /** @psalm-suppress PropertyTypeCoercion */
        $this->b = $injector->getInstance(DateTimeInterface::class); // @phpstan-ignore-line
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->m = $injector->getInstance('a'); // @phpstan-ignore-line
    }
}
