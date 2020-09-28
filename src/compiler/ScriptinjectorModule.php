<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

class ScriptinjectorModule extends AbstractModule
{
    /**
     * @var string
     */
    private $scriptDir;

    public function __construct(string $scriptDir, AbstractModule $module = null)
    {
        $this->scriptDir = $scriptDir;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(InjectorInterface::class)->toInstance(new ScriptInjector($this->scriptDir));
    }
}
