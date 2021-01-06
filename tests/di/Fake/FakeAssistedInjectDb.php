<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Assisted;
use Ray\Di\Di\Inject;

class FakeAssistedInjectDb
{
    /**
     * @return array [int, FakeAbstractDb]
     */
    public function getUser($id, #[Inject] ?FakeAbstractDb $db = null)
    {
        return [$id, $db];
    }
}
