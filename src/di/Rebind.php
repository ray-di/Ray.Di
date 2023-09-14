<?php

declare(strict_types=1);

namespace Ray\Di;

final class Rebind
{
    /** @var list<list<string>> */
    private $rebind = [];

    public function bind(string $fromInterface, string $toName, string $fromName = '', string $toInterface = ''): void
    {
        $this->rebind[] = [$fromInterface, $toName, $fromName, $toInterface];
    }

    public function commit(Container $container): void
    {
        foreach ($this->rebind as [$fromInterface, $toName, $fromName, $toInterface]) {
            $container->move($fromInterface, $fromName, $toInterface, $toName);
        }

        $this->rebind = [];
    }
}
