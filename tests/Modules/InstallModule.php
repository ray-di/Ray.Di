<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\SalesTax,
Ray\Di\Tests\TaxCharger;

class InstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new \Ray\Di\Modules\BasicModule);
        $this->bind('Ray\Di\Mock\LogInterface')->to('Ray\Di\Mock\Log');
    }
}
