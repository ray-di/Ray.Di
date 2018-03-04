<?php
namespace Ray\Di;

class FakeScalarType
{
    public function stringId(string $id)
    {
        unset($id);
    }
}
