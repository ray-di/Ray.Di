<?php

declare(strict_types=1);

namespace Ray\Di;


use Ray\Di\Di\Set;
use Ray\Di\MultiBinding\Map;

final class FakeSetNotFoundWithMap
{
    /**
     * @var Map<FakeEngineInterface>
     */
    public $engines;

    /**
     * This property should Set annotated for setProviderButNotSetFound method.
     * SetNotFound exception will be thrown.
     */
    public $engineProvider;

    public function __construct(
        Map $engines
    ){
        $this->engines = $engines;
    }
}
