<?php

//$instance = new FakeCar(new FakeEngine, 1, 'string', $singleton('Ray\Di\FooInterface-*'));
$instance->setHandle(new FakeHandle());
$instance->postConstruct();

return $instance;
