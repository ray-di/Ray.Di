<?php
namespace Ray\Di;

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

    public $gearStick;

    /**
     * Inject annotation at constructor is just for human, not mandatory.
     */
    public function __construct(FakeEngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @Inject 
     *
     * @return void
     */
    public function setTires(FakeTyreInterface $frontTyre, FakeTyreInterface $rearTyre): void
    {
        $this->frontTyre = $frontTyre;
        $this->rearTyre = $rearTyre;
    }

    /**
     * @Inject (optional=true)
     *
     * @return void
     */
    public function setHardtop(FakeHardtopInterface $hardtop): void
    {
        $this->hardtop = $hardtop;
    }

    /**
     * @Inject 
     *
     * @Named("rightMirror=right,$leftMirror=left")
     *
     * @return void
     */
    public function setMirrors(FakeMirrorInterface $rightMirror, FakeMirrorInterface $leftMirror): void
    {
        $this->rightMirror = $rightMirror;
        $this->leftMirror = $leftMirror;
    }

    /**
     * @Inject 
     *
     * @Named("right")
     *
     * @return void
     */
    public function setSpareMirror(FakeMirrorInterface $rightMirror): void
    {
        $this->spareMirror = $rightMirror;
    }

    /**
     * @Inject 
     *
     * @return void
     */
    public function setHandle(FakeHandleInterface $handle): void
    {
        $this->handle = $handle;
    }

    /**
     * @FakeGearStickInject ("leather")
     *
     * @return void
     */
    public function setGearStick(FakeGearStickInterface $stick): void
    {
        $this->gearStick = $stick;
    }

    /**
     * @PostConstruct 
     *
     * @return void
     */
    public function postConstruct(): void
    {
        $isEngineInstalled = $this->engine instanceof FakeEngine;
        $isTyreInstalled = $this->frontTyre instanceof FakeTyre;
        if ($isEngineInstalled && $isTyreInstalled) {
            $this->isConstructed = true;
        }
    }
}
