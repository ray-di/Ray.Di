<?php
namespace Ray\Di;

class FakeAnnoModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(FakeAnnoMethod1::class),
            [FakeAnnoInterceptor1::class]
        );
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(FakeAnnoMethod2::class),
            [FakeAnnoInterceptor2::class]
        );
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(FakeAnnoMethod3::class),
            [FakeAnnoInterceptor3::class]
        );
        $this->bindPriorityInterceptor(
            $this->matcher->annotatedWith(FakeAnnoClass::class),
            $this->matcher->any(),
            [FakeAnnoInterceptor4::class]
        );
        $this->bindInterceptor(
            $this->matcher->annotatedWith(FakeAnnoClass::class),
            $this->matcher->any(),
            [FakeAnnoInterceptor5::class]
        );
    }
}
