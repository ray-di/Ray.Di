<?php

declare(strict_types=1);

namespace Ray\Di;


use Ray\Di\Di\Set;

final class FakeSet
{
    /** @var Provider */
    public $provider;

    public function __construct(#[Set(FakeEngineInterface::class)] ProviderInterface $provider)
    {
        $this->provider = $provider;
    }
}
