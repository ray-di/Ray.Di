<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\Scope;
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
}

class WriterProvider implements ProviderInterface
{
    public function get()
    {
        return new Writer;
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
//        $this->closure = function(){};
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

class DiCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $injector = Injector::create([new DiaryModule]);
        $injector->getInstance('Ray\Di\DiaryInterface');
        $DiCompiler = new DiCompiler($injector, new CompileLogger(new Logger()));
        $injector = $DiCompiler->compile('Ray\Di\DiaryInterface');
        $instance = $injector->getInstance('Ray\Di\DiaryInterface');

        /** @var $instance \Ray\Di\Diary */
        $this->assertInstanceOf('Ray\Di\Diary', $instance);
        $this->assertInstanceOf('Ray\Di\Db', $instance->db);
        $this->assertInstanceOf('Ray\Di\Log', $instance->log);
        $this->assertInstanceOf('Ray\Di\Log', $instance->db->log);
        $this->assertInstanceOf('Ray\Di\Writer', $instance->writer);
        $this->assertSame('my dsn', $instance->db->dsn);

        return $instance;
    }

    /**
     * @depends testGetInstance
     */
    public function testPrototype($instance)
    {
        $dbHash1 = spl_object_hash($instance->log);
        $dbHash2 = spl_object_hash($instance->db->log);
        $this->assertNotSame($dbHash1, $dbHash2);
    }

    public function testSingleton()
    {
        $injector = Injector::create([new DiarySingletonModule]);
        $DiCompiler = new DiCompiler($injector, new CompileLogger(new Logger()));

        $compileInjector = $DiCompiler->compile('Ray\Di\DiaryInterface');

        $instance = $compileInjector->getInstance('Ray\Di\DiaryInterface');
        $dbHash1 = spl_object_hash($instance->log);
        $dbHash2 = spl_object_hash($instance->db->log);
        $this->assertSame($dbHash1, $dbHash2);

        return $compileInjector;
    }

    /**
     * @param $injector
     *
     * @depends testSingleton
     */
    public function testSerialize($injector)
    {
        $cached = serialize($injector);
        $this->assertInternalType('string', $cached);

        return $cached;
    }

    /**
     * @param $cached
     *
     * @depends testSerialize
     */
    public function testUnSerialized($cached)
    {
        $injector = unserialize($cached);
        /** @var $injector InstanceInterface */
        $instance = $injector->getInstance('Ray\Di\DiaryInterface');
        $this->assertInstanceOf('Ray\Di\Diary', $instance);
        $this->assertInstanceOf('Ray\Di\Db', $instance->db);
        $this->assertInstanceOf('Ray\Di\Log', $instance->log);
        $this->assertInstanceOf('Ray\Di\Log', $instance->db->log);
        $this->assertInstanceOf('Ray\Di\Writer', $instance->writer);

        return $instance;
    }

    /**
     * @depends testGetInstance
     */
    public function testCachedPrototype($instance)
    {
        $dbHash1 = spl_object_hash($instance->log);
        $dbHash2 = spl_object_hash($instance->db->log);
        $this->assertNotSame($dbHash1, $dbHash2);
    }

    /**
     * @depends testSingleton
     */
    public function testCachedSingleton(DiCompiler $compileInjector)
    {
        $injector = unserialize(serialize($compileInjector));
        /** @var $injector InstanceInterface */
        $instance = $injector->getInstance('Ray\Di\DiaryInterface');
        $dbHash1 = spl_object_hash($instance->log);
        $dbHash2 = spl_object_hash($instance->db->log);
        $this->assertSame($dbHash1, $dbHash2);
    }

    /**
     * @depends testSingleton
     */
    public function testProvider(DiCompiler $compileInjector)
    {
        $compileInjector->compile('Ray\Di\WriterInterface');
        $instance = $compileInjector->getInstance('Ray\Di\WriterInterface');
        $this->assertInstanceOf('Ray\Di\Writer', $instance);
    }
    
    public function testAop()
    {
        $injector = Injector::create([new DiaryAopModule]);
        $DiCompiler = new DiCompiler($injector, new CompileLogger(new Logger()));
        $compileInjector = $DiCompiler->compile('Ray\Di\DiaryInterface');
        $compileInjector = unserialize(serialize($compileInjector));
        $diary = $compileInjector->getInstance('Ray\Di\DiaryInterface');
        $result = $diary->returnSame('b');
        $this->assertSame('aop-b', $result);

        return $diary;
    }

    /**
     * @depends testAop
     */
    public function testPostConstruct($diary)
    {
        $this->assertTrue($diary->init);
    }
}
