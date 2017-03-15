<?php
namespace Ray\Di;

class FakeAbstractDb
{
    public $dbId;

    public function __construct($id)
    {
        $this->dbId = $id;
    }
}
