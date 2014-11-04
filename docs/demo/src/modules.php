<?php

namespace Ray\Di\Demo;

use Ray\Di\AbstractModule;

class LinkedBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(ComputerInterface::class)->to(Computer::class);
    }
}

class ProviderBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LangInterface::class)->toProvider(PhpProvider::class);
        $this->bind()->annotatedWith('php_version')->toInstance('7.0');
    }
}

class BindingAnnotationModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LegInterface::class)->annotatedWith('left')->to(LeftLeg::class);
        $this->bind(LegInterface::class)->annotatedWith('right')->to(RightLeg::class);
    }
}

class ConstructorBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('php_version')->toInstance('7.0');
        $this->bind(LangInterface::class)->toConstructor(Php::class, 'version=php_version');
    }
}