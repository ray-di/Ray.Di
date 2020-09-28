<?php

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

class FakeInstanceModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('bool')->toInstance(true);
        $this->bind()->annotatedWith('null')->toInstance(null);
        $this->bind()->annotatedWith('int')->toInstance(1);
        $this->bind()->annotatedWith('float')->toInstance(1.0);
        $this->bind()->annotatedWith('string')->toInstance('ray');
        $this->bind()->annotatedWith('no_index_array')->toInstance([1, 2]);
        $this->bind()->annotatedWith('assoc')->toInstance(['a' => 1]);
        $this->bind()->annotatedWith('object')->toInstance(new \DateTime());
    }
}
