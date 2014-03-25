<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;

require_once __DIR__ . '/Mock/Diary/diary_classes.php';

class DiCompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $injector;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $tmpDir;

    protected function setUp()
    {
        $config = new Config(
            new Annotation(
                new Definition,
                new AnnotationReader
            )
        );
        $logger = new CompileLogger(new Logger);
        $logger->setConfig($config);
        $this->injector = new Injector(
            new Container(new Forge($config)),
            new DiaryAopModule,
            new Bind,
            new Compiler(
                sys_get_temp_dir(),
                new PHPParser_PrettyPrinter_Default
            ),
            $logger
        );
        $this->logger = $logger;
        $this->tmpDir = __DIR__ . '/tmp';
    }

    public function testGetInstance()
    {
        $DiCompiler = new DiCompiler($this->injector, $this->logger, new ArrayCache, $this->tmpDir);
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
        $this->injector->setModule(new DiarySingletonModule);
        $DiCompiler = new DiCompiler($this->injector, $this->logger, new ArrayCache, $this->tmpDir);

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

        $DiCompiler = new DiCompiler($this->injector, $this->logger, new ArrayCache, $this->tmpDir);
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

    public function testCreate()
    {
        // cache create
        $cache = new FilesystemCache(__DIR__ . '/tmp');
        $tmpDir = __DIR__ . '/tmp';
        $moduleProvider = function() {
            return new DiaryAopModule;
        };
        $injector = DiCompiler::create($moduleProvider, $cache, 'diary', $tmpDir);

        $injector->getInstance('Ray\Di\DiaryInterface');
        /** @var $insjector $injector */
        $injector = DiCompiler::create($moduleProvider, $cache, 'diary', $tmpDir);
        $instance = $injector->getInstance('Ray\Di\DiaryInterface');
        $this->assertInstanceOf('Ray\Di\Diary', $instance);
    }

    /**
     * @runTestsInSeparateProcesses
     */
    public function testCached()
    {
        exec('php ' . __DIR__ . '/scripts/cache_diary.php', $return);
        $this->assertSame($return[0], 'works');
    }

    /**
     * @depends testCached
     * @runTestsInSeparateProcesses
     */
    public function testCacheRead()
    {
        exec('php ' . __DIR__ . '/scripts/cache_diary.php', $return);
        $this->assertSame($return[0], 'works');
    }

    /**
     * @depends testCached
     * @runTestsInSeparateProcesses
     */
    public function testCacheReadWriter()
    {
        $injector = require __DIR__ . '/scripts/cache_compiler.php';
        $instance = $injector->getInstance('Ray\Di\WriterInterface');
        $this->assertInstanceOf('Ray\Di\Writer', $instance);
    }
}
