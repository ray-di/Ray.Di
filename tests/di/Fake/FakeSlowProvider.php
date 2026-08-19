<?php

declare(strict_types=1);

namespace Ray\Di;

use Swoole\Coroutine;

/**
 * Provider that suspends mid-construction, simulating coroutine IO
 * (e.g. a connection pool wait) inside object construction
 *
 * @implements ProviderInterface<FakeSlowResource>
 */
class FakeSlowProvider implements ProviderInterface
{
    public static int $buildCount = 0;

    public function get()
    {
        self::$buildCount++;
        if (Coroutine::getCid() > 0) {
            Coroutine::sleep(0.05);
        }

        return new FakeSlowResource();
    }
}
