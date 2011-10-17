<?php
namespace Ray\Di;
class MockChildClass extends MockParentClass
{
    protected $zim;

    protected $fake;

    protected $fake1;

    protected $fake2;

    public function __construct($foo, MockOtherClass $zim)
    {
        parent::__construct($foo);
        $this->zim = $zim;
    }

    public function setFake($fake)
    {
        $this->fake = $fake;
    }

    public function getFake()
    {
        return $this->fake;
    }

    public function setFakeDouble($fake1, $fake2)
    {
        $this->fake1 = $fake1;
        $this->fake2 = $fake2;
    }

    public function getFakeDouble()
    {
        return array($this->fake1, $this->fake2);
    }

}
