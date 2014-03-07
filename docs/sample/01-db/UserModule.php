<?php

use Ray\Di\AbstractModule;

class UserModule extends AbstractModule
{
    protected function configure()
    {
        // bind dependency @Inject @Named("pdo_user")
        $pdo = new \PDO('sqlite::memory:', null, null);
        $this->bind('PDO')->annotatedWith('pdo_user')->toInstance($pdo);

        // bind aspect @Transaction
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Annotation\Transactional'),
            [new Transaction]
        );
        // bind aspect @Template
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Annotation\Template'),
            [new Timer, new TemplateInterceptor]
        );
    }
}
