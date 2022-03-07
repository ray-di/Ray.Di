<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Set;

final class FakeSet
{
    /** @var Provider */
    public $provider;

    /**
     * @var ProviderInterface<FakeEngineInterface>
     * @Set(FakeEngineInterface::class)
     */
    public $engineProvider;

    public function __construct(#[Set(FakeEngineInterface::class)] ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @Inject
     */
    public function setProviderWithAnnotation(ProviderInterface $engineProvider)
    {
        $this->engineProvider = $engineProvider;
    }
}
