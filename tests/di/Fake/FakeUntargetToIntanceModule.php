<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class FakeUntargetToIntanceModule extends AbstractModule
{
    protected function configure()
    {
        $instance = new FakeUntarget(new FakeUntargetChild(1));
        $this->bind(FakeUntarget::class)->toInstance($instance);
    }
}
