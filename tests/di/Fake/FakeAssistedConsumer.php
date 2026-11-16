<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;

use function array_slice;
use function func_get_args;

class FakeAssistedConsumer
{
    /**
     * @return FakeRobotInterface|null
     */
    public function assistOne($a, $b, #[Assisted] ?FakeRobotInterface $robot = null)
    {
        unset($a, $b);

        return $robot;
    }

    public function assistWithName($a, #[Assisted] #[Named('one')] $var1 = null)
    {
        unset($a);

        return $var1;
    }

    /**
     * @return (FakeRobotInterface|mixed|null)[]
     * @psalm-return array{0: mixed, 1: FakeRobotInterface|null}
     */
    public function assistAny(#[Assisted] #[Named('one')] $var2 = null, #[Assisted] ?FakeRobotInterface $robot = null)
    {
        return [$var2, $robot];
    }

    /** @return array{string|null, FakeRobotInterface|null} */
    public function assistAfterDefault(?string $value = 'default', #[Assisted] ?FakeRobotInterface $robot = null): array
    {
        return [$value, $robot];
    }

    /** @return array{string, FakeRobotInterface|null, array<array-key, string>} */
    public function assistBeforeVariadic(string $value, #[Assisted] ?FakeRobotInterface $robot = null, string ...$rest): array
    {
        return [$value, $robot, $rest];
    }

    /** @return array{string, FakeRobotInterface|null, list<mixed>} */
    public function assistWithExtraArguments(string $value, #[Assisted] ?FakeRobotInterface $robot = null): array
    {
        return [$value, $robot, array_slice(func_get_args(), 2)];
    }
}
