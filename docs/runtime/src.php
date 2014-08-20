<?php

namespace Ray\Di\Sample;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Named;
use Ray\Aop\MethodInterceptor;

interface MovieFinderInterface
{
}

/**
 * NotOnWeekends
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Timer
{
}

class TimerLogger implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $time = microtime(true);
        $result = $invocation->proceed();
        error_log(microtime(true) - $time);

        return $result;
    }
}

class MovieFinder implements MovieFinderInterface
{
}

interface MovieListerInterface
{
}

class MovieLister implements MovieListerInterface
{
    /**
     * @var MovieFinderInterface
     */
    public $finder;

    /**
     * @Inject
     */
    public function __construct(MovieFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @Ray\Di\Sample\Timer
     */
    public function find()
    {
    }
}

class MovieListerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Sample\MovieFinderInterface')->to('Ray\Di\Sample\MovieFinder');
        $this->bind('Ray\Di\Sample\MovieListerInterface')->to('Ray\Di\Sample\MovieLister');
        $this->bindInterceptor(
             $this->matcher->any(),
             $this->matcher->annotatedWith('\Ray\Di\Sample\Timer'),
             [$this->requestInjection('Ray\Di\Sample\TimerLogger')]
        );
    }
}
