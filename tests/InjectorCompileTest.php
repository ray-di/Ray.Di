<?php

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

interface DoublerInterface
{
    public function double($num);
}

class Doubler implements DoublerInterface
{
    public function double($num)
    {
        return $num * 2;
    }
}

class Consumer
{
    private $doubler;

    /**
     * @param DoublerInterface $doubler
     *
     * @Ray\Di\Di\Inject
     */
    public function __construct(DoublerInterface $doubler)
    {
        $this->doubler = $doubler;
    }

    public function double($num)
    {
        return $this->doubler->double($num);
    }
}

class PlusOneInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return 1 + $invocation->proceed();
    }
}

class Module extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\DoublerInterface')->to('Ray\Di\Doubler');
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [new PlusOneInterceptor]
        );
    }
}

class CompilerTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function testSample()
    {
        $result = (new Consumer(new Doubler))->double(5);
        $this->assertSame(10, $result);
    }

    public function testCompiler()
    {
        $consumer = Injector::create([new Module])->getInstance('Ray\Di\Consumer');
        /** @var $consumer Consumer */
        $result = $consumer->double(5);
        // 12 = 5 * 2 + 1(Doubler::double<=PlusOneInterceptor) + 1(Consumer::double<=PlusOneInterceptor)
        $this->assertSame(12, $result);
    }
}
