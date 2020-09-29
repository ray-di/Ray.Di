<?php

declare(strict_types=1);

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
    public function setMirrors(FakeMirrorInterface $rightMirror): void
    {
        $this->rightMirror = $rightMirror;
    }

    /**
     * @Inject
     * @FakeLeft
     */
    public function setLeftMirror(FakeMirrorInterface $leftMirror): void
    {
        $this->leftMirror = $leftMirror;
    }
}
