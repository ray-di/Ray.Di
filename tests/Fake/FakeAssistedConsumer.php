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
     * @Assisted
     */
    public function assistOne($a, $b, FakeRobotInterface $robot = null)
    {
        return $robot;
    }

    /**
     * @Assisted
     * @Named("var1=one")
     */
    public function assistWithName($a, $var1 = null)
    {
        return $var1;
    }
}
