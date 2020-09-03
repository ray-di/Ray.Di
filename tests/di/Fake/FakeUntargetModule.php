<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class FakeUntargetModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeUntargetChild::class);
    }
}
