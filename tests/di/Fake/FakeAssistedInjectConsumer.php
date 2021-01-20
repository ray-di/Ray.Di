<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Annotation\FakeInjectOne;
use Ray\Di\Di\Assisted;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class FakeAssistedInjectConsumer
{
    public function assistOne($a, $b, #[Assisted] ?FakeRobotInterface $robot = null): ?FakeRobotInterface
    {
        unset($a, $b);

        return $robot;
    }

    public function assistWithName($a, #[Assisted, Named('one')] $var1 = null)
    {
        unset($a);

        return $var1;
    }

    /**
     * @return (FakeRobotInterface|mixed|null)[]
     * @psalm-return array{0: mixed, 1: FakeRobotInterface|null}
     */
    public function assistAny(#[Assisted, Named('one')] $var2 = null, #[Inject] ?FakeRobotInterface $robot = null)
    {
        return [$var2, $robot];
    }

    public function assistCustomeAssistedInject(#[FakeInjectOne] int $one = 0): int
    {
        return $one;
    }
}
