<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeAssistedDbProvider implements ProviderInterface
{
    /** @var MethodInvocationProvider */
    private $invocationProvider;

    public function __construct(MethodInvocationProvider $invocationProvider)
    {
        $this->invocationProvider = $invocationProvider;
    }

    public function get()
    {
        $parameters = $this->invocationProvider->get()->getArguments()->getArrayCopy();
        [$id] = $parameters;

        return new FakeAssistedDb($id);
    }
}
