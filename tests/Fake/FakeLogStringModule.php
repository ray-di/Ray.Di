<?php
namespace Ray\Di;

use stdClass;

class FakeLogStringModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('null')->toInstance(null);
        $this->bind()->annotatedWith('bool')->toInstance(true);
        $this->bind()->annotatedWith('int')->toInstance(1);
        $this->bind()->annotatedWith('string')->toInstance('1');
        $this->bind()->annotatedWith('array')->toInstance([1]);
        $this->bind()->annotatedWith('object')->toInstance(new stdClass);
        $this->bind(FakeAopInterface::class)->to(FakeAop::class);
        $this->bind(FakeRobotInterface::class)->toProvider(FakeRobotProvider::class)->in(Scope::SINGLETON);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->startsWith('returnSame'),
            [FakeDoubleInterceptor::class]
        );
    }
}
