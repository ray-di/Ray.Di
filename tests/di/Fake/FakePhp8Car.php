<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

class FakePhp8Car implements FakeCarInterface
{
    public $engine;
    public $hardtop;
    public $frontTyre;
    public $rearTyre;
    public $isConstructed = false;
    public $rightMirror;
    public $constructerInjectedRightMirror;
    public $leftMirror;
    public $spareMirror;

    /** @var FakeHandleInterface */
    public $handle;
    public $gearStick;

    public function __construct(FakeEngineInterface $engine, #[Named('right')] FakeMirrorInterface $rightMirror)
    {
        $this->engine = $engine;
        $this->constructerInjectedRightMirror = $rightMirror;
    }

    #[Inject]
    public function setTires(FakeTyreInterface $frontTyre, FakeTyreInterface $rearTyre): void
    {
        $this->frontTyre = $frontTyre;
        $this->rearTyre = $rearTyre;
    }

    #[Inject(optional: true)]
    public function setHardtop(FakeHardtopInterface $hardtop): void
    {
        $this->hardtop = $hardtop;
    }

    #[Inject]
    public function setMirrors(#[Named('right')] FakeMirrorInterface $rightMirror, #[Named('left')] FakeMirrorInterface $leftMirror): void
    {
        $this->rightMirror = $rightMirror;
        $this->leftMirror = $leftMirror;
    }

    /**
     * @FakeGearStickInject("leather")
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
