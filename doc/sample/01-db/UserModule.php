<?php

namespace Ray\Di\Sample;

use Ray\Di\AbstractModule,
    Ray\Di\Scope,
    Ray\Di\Sample\Transaction;

class UserModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $pdo = new \PDO('sqlite::memory:', null, null);
        $this->bind('PDO')->annotatedWith('pdo_user')->toInstance($pdo);
        $this->bindInterceptor($this->matcher->any(), $this->matcher->any(), [new Timer, new Transaction]);
        //$this->bindInterceptor($this->matcher->any(), $this->matcher->any(), [new Template]);
    }
}