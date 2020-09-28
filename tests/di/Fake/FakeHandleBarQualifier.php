<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeHandleBarQualifier
{
    public $rightMirror;
    public $leftMirror;

    /**
     * @FakeRight("rightMirror")
     * @FakeLeft("leftMirror")
     */
    public function __construct(FakeMirrorInterface $rightMirror, FakeMirrorInterface $leftMirror)
    {
        $this->rightMirror = $rightMirror;
        $this->leftMirror = $leftMirror;
    }
}
