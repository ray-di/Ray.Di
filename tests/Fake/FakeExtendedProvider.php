<?php
namespace Ray\Di;

class FakeExtendedProvider implements ProviderInterface
{
    public function get()
    {
        return new FakeExtendedClass;
    }
}
