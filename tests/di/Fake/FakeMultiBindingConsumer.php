<?php

declare(strict_types=1);

namespace Ray\Di;


use Ray\Di\Di\Set;
use Ray\Di\MultiBinding\Map;

final class FakeMultiBindingConsumer
{
    /** @var Map */
    public $engines;

    public function __construct(#[Set(FakeEngineInterface::class)] Map $engines)
    {
        $this->engines = $engines;
    }
}
