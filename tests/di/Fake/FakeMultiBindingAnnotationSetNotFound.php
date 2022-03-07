<?php

declare(strict_types=1);

namespace Ray\Di;


use Ray\Di\Di\Set;
use Ray\Di\MultiBinding\Map;

final class FakeMultiBindingAnnotationSetNotFound
{
    /**
     * @var Map<FakeEngineInterface>
     */
    public $engines;

    public function __construct(
        Map $engines
    ){
        $this->engines = $engines;
    }
}
