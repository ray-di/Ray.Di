<?php

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Scope;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;


interface DbInterface{}
interface DiaryInterface{}
interface LogInterface{}
interface WriterInterface{}

class Log implements LogInterface
{
}

class Writer implements WriterInterface
{
    private function __construct()
    {
    }

    public static function newInstance()
    {
        return new self;
    }
}

class WriterProvider implements ProviderInterface
{
    public function get()
    {
        return Writer::newInstance();
    }
}

class Db implements DbInterface
{
    public $dsn;
    public $log;

    /**
     * @Inject
     */
    public function setLog(LogInterface $log)
    {
        $this->log = $log;
    }

    /**
     * @Inject
     * @Named("dsn")
     */
    public function __construct($dsn)
    {
        $this->dsn = $dsn;
    }
}

class Diary implements DiaryInterface
{
    public $db;
    public $log;
    public $writer;
    public $init = false;
    /**
     * @param DbInterface $db
     *
     * @Inject
     */
    public function __construct(LogInterface $log, WriterInterface $writer, DbInterface $db)
    {
        $this->log = $log;
        $this->writer = $writer;
        $this->db = $db;
    }

    /**
     * @PostConstruct
     */
    public function init()
    {
        $this->init = true;
    }

    public function returnSame($a)
    {
        return $a;
    }
}

class DiaryModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('')->annotatedWith('dsn')->toInstance('my dsn');
        $this->bind('Ray\Di\LogInterface')->to('Ray\Di\Log');
        $this->bind('Ray\Di\DbInterface')->to('Ray\Di\Db');
        $this->bind('Ray\Di\WriterInterface')->toProvider('Ray\Di\WriterProvider');
        $this->bind('Ray\Di\DiaryInterface')->to('Ray\Di\Diary');
    }
}

class DiarySingletonModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\LogInterface')->to('Ray\Di\Log')->in(Scope::SINGLETON);
        $this->install(new DiaryModule);
    }
}

class DiaryInterceptor implements MethodInterceptor
{
    public $log;
    public $dsn;
    private $closure;


    /**
     * @Inject
     * @Named("dsn")
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * @Inject
     */
    public function __construct(LogInterface $log)
    {
        $this->log = $log;
        $this->closure = function () {};
    }

    public function invoke(MethodInvocation $invocation)
    {
        return 'aop-' . $invocation->proceed();
    }
}

class DiaryAopModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new DiaryModule);
        $diaryInterceptor = $this->requestInjection('Ray\Di\DiaryInterceptor');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Ray\Di\Diary'),
            $this->matcher->any(),
            [$diaryInterceptor]
        );
    }
}

class DiaryAopErrorModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new DiaryModule);
        $diaryInterceptor = $this->requestInjection('Ray\Di\DiaryInterceptor');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Ray\Di\Diary'),
            $this->matcher->any(),
            [new \Ray\Di\DiaryInterceptor(new Log)]
        );
    }
}
