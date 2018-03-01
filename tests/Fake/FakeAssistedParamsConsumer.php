<?php
namespace Ray\Di;

use Ray\Di\Di\Assisted;

class FakeAssistedParamsConsumer
{
    /**
     * @Assisted("db")
     *
     * @return array [int, FakeAbstractDb]
     */
    public function getUser($id, FakeAbstractDb $db = null)
    {
        return [$id, $db];
    }
}
