<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Inject;

class FakeParamInjectConsumer
{
    public function assistOne($a, $b, #[Inject] ?FakeRobotInterface $robot = null): ?FakeRobotInterface
    {
        unset($a, $b);

        return $robot;
    }
}
