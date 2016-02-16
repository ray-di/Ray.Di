<?php
/**
 * Created by PhpStorm.
 * User: akihito
 * Date: 2016/02/14
 * Time: 8:41
 */

namespace Ray\Di;

use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;

class FakeAssistedConsumer
{
    /**
     * @Assisted({"robot"})
     */
    public function assistOne($a, $b, FakeRobotInterface $robot)
    {
        return $robot;
    }

    /**
     * @Assisted({"var1"})
     * @Named("var1=one")
     */
    public function assistWithName($a, $var1)
    {
        return $var1;
    }
}
