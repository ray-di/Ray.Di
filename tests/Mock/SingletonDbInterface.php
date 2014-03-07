<?php

namespace Ray\Di\Mock;

use Ray\Di\Di\Scope;

/**
 * @Scope("Singleton")
 */
interface SingletonDbInterface extends DbInterface
{
}
