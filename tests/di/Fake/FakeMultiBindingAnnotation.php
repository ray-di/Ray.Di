<?php

declare(strict_types=1);

namespace Ray\Di;


use Ray\Di\Di\Set;
use Ray\Di\MultiBinding\Map;

final class FakeMultiBindingAnnotation
{
    /**
     * @var Map<FakeEngineInterface>
     * @Set(FakeEngineInterface::class)
     */
    public $engines;

    /**
     * @var Map<FakeRobotInterface>
     * @Set(FakeRobotInterface::class)
     */
    public $robots;

    public function __construct(
        Map $engines,
        Map $robots
    ){
        $this->engines = $engines;
        $this->robots = $robots;
    }
}
