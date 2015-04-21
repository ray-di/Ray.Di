<?php

namespace Ray\Di;

use Ray\Aop\Matcher;

class DependencyCompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dependency
     */
    private $dependency;

    public function testInstanceCompileString()
    {
        $dependencyInstance = new Instance('bear');
        $code = (new DependencyCompiler(new Container))->compile($dependencyInstance);
        $expected = <<<'EOT'
<?php

return 'bear';
EOT;
        $this->assertSame($expected, (string)$code);
    }

    public function testInstanceCompileInt()
    {
        $dependencyInstance = new Instance((int)1);
        $code = (new DependencyCompiler(new Container))->compile($dependencyInstance);
        $expected = <<<'EOT'
<?php

return 1;
EOT;
        $this->assertSame($expected, (string)$code);
    }

    public function testInstanceCompileArray()
    {
        $dependencyInstance = new Instance([1, 2, 3]);
        $code = (new DependencyCompiler(new Container))->compile($dependencyInstance);
        $expected = <<<'EOT'
<?php

return array(1, 2, 3);
EOT;
        $this->assertSame($expected, (string)$code);
    }

    public function testDependencyCompile()
    {
        $container = (new FakeCarModule)->getContainer();
        $code = (new DependencyCompiler($container))->compileIndex('Ray\Di\FakeCarInterface-*');
        $expected = <<<'EOT'
<?php

namespace Ray\Di\Compiler;

$instance = new \Ray\Di\FakeCar($prototype('Ray\\Di\\FakeEngineInterface-*'));
$instance->setTires($prototype('Ray\\Di\\FakeTyreInterface-*'), $prototype('Ray\\Di\\FakeTyreInterface-*'));
$instance->setHardtop($prototype('Ray\\Di\\FakeHardtopInterface-*'));
$instance->setMirrors($singleton('Ray\\Di\\FakeMirrorInterface-right'), $singleton('Ray\\Di\\FakeMirrorInterface-left'));
$instance->setSpareMirror($singleton('Ray\\Di\\FakeMirrorInterface-right'));
$instance->setHandle($prototype('Ray\\Di\\FakeHandleInterface-*'));
$instance->postConstruct();
return $instance;
EOT;
        $this->assertSame($expected, (string)$code);
    }

    public function testDependencyInstanceCompile()
    {
        $container = (new FakeCarModule)->getContainer();
        $code = (new DependencyCompiler($container))->compileIndex('Ray\Di\FakeHandleInterface-*');
        $expected = <<<'EOT'
<?php

namespace Ray\Di\Compiler;

$instance = new \Ray\Di\FakeHandleProvider('momo');
return $instance->get();
EOT;
        $this->assertSame($expected, (string)$code);
    }
}
