<?php
namespace Ray\Di;

use Ray\Di\Di\Inject;

class FakeHandleBar
{
    public $rightMirror;

    public $leftMirror;

    /**
     * @Inject
     * @FakeRight
     */
    public function setMirrors(FakeMirrorInterface $rightMirror)
    {
        $this->rightMirror = $rightMirror;
    }

    /**
     * @Inject
     * @FakeLeft
     */
    public function setLeftMirror(FakeMirrorInterface $leftMirror)
    {
        $this->leftMirror = $leftMirror;
    }
}
