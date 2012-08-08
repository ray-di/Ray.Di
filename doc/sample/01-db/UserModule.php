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
        // bind dependency @Inject @Named("pdo_user")
        $pdo = new \PDO('sqlite::memory:', null, null);
        $this->bind('PDO')->annotatedWith('pdo_user')->toInstance($pdo);

        // bind aspect @Transaction
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Ray\Di\Sample\Transactional'),
            [new Transaction]
        );
        // bind aspect @Template
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Ray\Di\Sample\Template'),
            [new Timer, new TemplateInterceptor]
        );
    }
}
