<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

class FakeAttributeCar implements FakeCarInterface
{
    public $engine;
    public $hardtop;
    public $frontTyre;
    public $rearTyre;
    public $isConstructed = false;
    public $rightMirror;
    public $leftMirror;
    public $spareMirror;

    /** @var FakeHandleInterface */
    public $handle;
    public $gearStick;

    /**
     * Inject annotation at constructor is just for human, not mandatory.
     */
    public function __construct(FakeEngineInterface $engine)
    {
        $this->engine = $engine;
    }

    #[Inject]
    public function setTires(FakeTyreInterface $frontTyre, FakeTyreInterface $rearTyre): void
    {
        $this->frontTyre = $frontTyre;
        $this->rearTyre = $rearTyre;
    }

    #[Inject(['optional' => true])]
    public function setHardtop(FakeHardtopInterface $hardtop): void
    {
        $this->hardtop = $hardtop;
    }

    #[Inject(['optional' => true])]
    /**
     * @Named("rightMirror=right,$leftMirror=left")
     */
    public function setMirrors(FakeMirrorInterface $rightMirror, FakeMirrorInterface $leftMirror): void
    {
        $this->rightMirror = $rightMirror;
        $this->leftMirror = $leftMirror;
    }

    #[Inject]
    /**
     * @Named("right")
     */
    public function setSpareMirror(FakeMirrorInterface $rightMirror): void
    {
        $this->spareMirror = $rightMirror;
    }

    #[Inject]
    public function setHandle(FakeHandleInterface $handle): void
    {
        $this->handle = $handle;
    }

    /**
     * @FakeGearStickInject ("leather")
     */
    public function setGearStick(FakeGearStickInterface $stick): void
    {
        $this->gearStick = $stick;
    }

    #[PostConstruct]
    public function postConstruct(): void
    {
        $isEngineInstalled = $this->engine instanceof FakeEngine;
        $isTyreInstalled = $this->frontTyre instanceof FakeTyre;
        if ($isEngineInstalled && $isTyreInstalled) {
            $this->isConstructed = true;
        }
    }
}
