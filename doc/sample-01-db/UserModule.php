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
        $this->registerInterceptAnnotation('Transactional', array(new Timer, new Transaction));
        $this->registerInterceptAnnotation('Template', array(new Template));
    }
}