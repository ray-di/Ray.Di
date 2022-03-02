<?php

declare(strict_types=1);

namespace Ray\Di;


use Ray\Di\Di\Set;
use Ray\Di\MultiBinding\Map;

final class FakeMultiBindingConsumer
{
    /** @var Map<FakeEngineInterface> */
    public $engines;

    /** @param Map<FakeEngineInterface> $engines */
    public function __construct(#[Set(FakeEngineInterface::class)] Map $engines)
    {
        $this->engines = $engines;
    }
}
