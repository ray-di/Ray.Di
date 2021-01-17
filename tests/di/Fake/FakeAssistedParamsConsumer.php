<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Assisted;

class FakeAssistedParamsConsumer
{
    /**
     * @return array [int, FakeAbstractDb]
     *
     * @Assisted({"db"})
     */
    public function getUser($id, ?FakeAbstractDb $db = null)
    {
        return [$id, $db];
    }
}
