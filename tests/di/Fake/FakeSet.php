<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Set;

final class FakeSet
{
    /**
     * @param Provider<FakeEngineInterface> $provider
     */
    public function __construct(#[Set(FakeEngineInterface::class)] public ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function warn(): void
    {
        // valid method
        $this->provider->get()->foo();

        // invalid method (but phpstan does not detect the error)
        /** @psalm-suppress UndefinedInterfaceMethod */
        $this->provider->get()->bar();
    }
}
