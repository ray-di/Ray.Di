<?php

declare(strict_types=1);

use Ray\Di\FakeCar;

/** @var FakeCar $instance */

//$instance = new FakeCar(new FakeEngine, 1, 'string', $singleton('Ray\Di\FooInterface-*'));
$instance->setHandle(new FakeHandle());
$instance->postConstruct();

return $instance;
