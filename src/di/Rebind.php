<?php

declare(strict_types=1);

namespace Ray\Di;

final class Rebind
{
    /** @var list<list<string>> */
    private $reBind = [];

    public function bind(string $fromInterface, string $toName, string $fromName = '', string $toInterface = ''): void
    {
        $this->reBind[] = [$fromInterface, $toName, $fromName, $toInterface];
    }

    public function commit(Container $container): void
    {
        foreach ($this->reBind as [$fromInterface, $toName, $fromName, $toInterface]) {
            $container->move($fromInterface, $fromName, $toInterface, $toName);
        }

        $this->reBind = [];
    }
}
