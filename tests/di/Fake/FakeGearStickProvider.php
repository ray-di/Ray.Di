<?php
namespace Ray\Di;

use InvalidArgumentException;

class FakeGearStickProvider implements ProviderInterface
{
    /**
     * @var InjectionPointInterface
     */
    private $ip;

    public function __construct(InjectionPointInterface $ip)
    {
        $this->ip = $ip;
    }

    public function get()
    {
        $qualifiers = $this->ip->getQualifiers();
        $type = null;

        foreach ($qualifiers as $qualifier) {
            if ($qualifier instanceof FakeGearStickInject) {
                $type = $qualifier->value;
            }
        }

        if ($type !== 'leather') {
            throw new InvalidArgumentException('Invalid Gear Stick Type');
        }

        return new FakeLeatherGearStick;
    }
}
