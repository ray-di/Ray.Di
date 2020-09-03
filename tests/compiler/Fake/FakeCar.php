<?php

namespace Ray\Compiler;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

class FakeCar implements FakeCarInterface
{
    public $engine;

    public $hardtop;

    public $frontTyre;

    public $rearTyre;

    public $isConstructed = false;

    public $rightMirror;

    public $leftMirror;

    public $spareMirror;

    /**
     * @var FakeHandleInterface
     */
    public $handle;

    public $null = false;

    /**
     * @Inject
     */
    public function setTires(FakeTyreInterface $frontTyre, FakeTyreInterface $rearTyre, $null = null)
    {
        $this->frontTyre = $frontTyre;
        $this->rearTyre = $rearTyre;
        $this->null = $null;
    }

    /**
     * @Inject(optional=true)
     */
    public function setHardtop(FakeHardtopInterface $hardtop)
    {
        $this->hardtop = $hardtop;
    }

    /**
     * @Inject
     * @Named("rightMirror=right,leftMirror=left")
     */
    public function setMirrors(FakeMirrorInterface $rightMirror, FakeMirrorInterface $leftMirror)
    {
        $this->rightMirror = $rightMirror;
        $this->leftMirror = $leftMirror;
    }

    /**
     * @Inject
     * @Named("right")
     */
    public function setSpareMirror(FakeMirrorInterface $rightMirror)
    {
        $this->spareMirror = $rightMirror;
    }

    /**
     * @Inject
     */
    public function setHandle(FakeHandleInterface $handle)
    {
        $this->handle = $handle;
    }

    /**
     * Inject annotation at constructor is just for human, not mandatory.
     */
    public function __construct(FakeEngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @PostConstruct
     */
    public function postConstruct()
    {
        $isEngineInstalled = $this->engine instanceof FakeEngine;
        $isTyreInstalled = $this->frontTyre instanceof FakeTyre;
        if ($isEngineInstalled && $isTyreInstalled) {
            $this->isConstructed = true;
        }
    }
}
