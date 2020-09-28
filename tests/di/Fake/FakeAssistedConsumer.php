<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;

class FakeAssistedConsumer
{
    /**
     * @return FakeRobotInterface|null
     *
     * @Assisted({"robot"})
     */
    public function assistOne($a, $b, ?FakeRobotInterface $robot = null)
    {
        unset($a, $b);

        return $robot;
    }

    /**
     * @Assisted({"var1"})
     * @Named("var1=one")
     */
    public function assistWithName($a, $var1 = null)
    {
        unset($a);

        return $var1;
    }

    /**
     * @return (FakeRobotInterface|mixed|null)[]
     * @psalm-return array{0: mixed, 1: FakeRobotInterface|null}
     *
     * @Assisted({"var2", "robot"})
     * @Named("var2=one")
     */
    public function assistAny($var2 = null, ?FakeRobotInterface $robot = null)
    {
        return [$var2, $robot];
    }
}
