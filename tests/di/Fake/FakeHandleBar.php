<?php
namespace Ray\Di;

use Ray\Di\Di\Inject;

class FakeHandleBar
{
    public $rightMirror;

    public $leftMirror;

    /**
     * @Inject 
     *
     * @FakeRight 
     *
     * @return void
     */
    public function setMirrors(FakeMirrorInterface $rightMirror): void
    {
        $this->rightMirror = $rightMirror;
    }

    /**
     * @Inject 
     *
     * @FakeLeft 
     *
     * @return void
     */
    public function setLeftMirror(FakeMirrorInterface $leftMirror): void
    {
        $this->leftMirror = $leftMirror;
    }
}
