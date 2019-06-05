<?php
namespace Ray\Di;

use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;

class FakeAssistedConsumer
{
    /**
     * @Assisted({"robot"})
     */
    public function assistOne($a, $b, FakeRobotInterface $robot = null)
    {
        return $robot;
    }

    /**
     * @Assisted({"var1"})
     * @Named("var1=one")
     */
    public function assistWithName($a, $var1 = null)
    {
        return $var1;
    }

    /**
     * @Assisted({"var2", "robot"})
     * @Named("var2=one")
     */
    public function assistAny($var2 = null, FakeRobotInterface $robot = null)
    {
        return [$var2, $robot];
    }
}
