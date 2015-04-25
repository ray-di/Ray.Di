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
        $this->assertSame($expected, (string) $code);
    }

    public function testInstanceCompileInt()
    {
        $dependencyInstance = new Instance((int) 1);
        $code = (new DependencyCompiler(new Container))->compile($dependencyInstance);
        $expected = <<<'EOT'
<?php

return 1;
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testInstanceCompileArray()
    {
        $dependencyInstance = new Instance([1, 2, 3]);
        $code = (new DependencyCompiler(new Container))->compile($dependencyInstance);
        $expected = <<<'EOT'
<?php

return array(1, 2, 3);
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyCompile()
    {
        $container = (new FakeCarModule)->getContainer();
        $dependency = $container->getContainer()['Ray\Di\FakeCarInterface-*'];
        $code = (new DependencyCompiler($container))->compile($dependency);
        $expected = <<<'EOT'
<?php

namespace Ray\Di\Compiler;

$instance = new \Ray\Di\FakeCar($prototype('Ray\\Di\\FakeEngineInterface-*'));
$instance->setTires($prototype('Ray\\Di\\FakeTyreInterface-*'), $prototype('Ray\\Di\\FakeTyreInterface-*'));
$instance->setHardtop($prototype('Ray\\Di\\FakeHardtopInterface-*'));
$instance->setMirrors($singleton('Ray\\Di\\FakeMirrorInterface-right'), $singleton('Ray\\Di\\FakeMirrorInterface-left'));
$instance->setSpareMirror($singleton('Ray\\Di\\FakeMirrorInterface-right'));
$instance->setHandle($prototype('Ray\\Di\\FakeHandleInterface-*', array('Ray\\Di\\FakeCar', 'setHandle', 'handle')));
$instance->postConstruct();
return $instance;
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyProviderCompile()
    {
        $container = (new FakeCarModule)->getContainer();
        $dependency = $container->getContainer()['Ray\Di\FakeHandleInterface-*'];
        $code = (new DependencyCompiler($container))->compile($dependency);
        $expected = <<<'EOT'
<?php

namespace Ray\Di\Compiler;

$instance = new \Ray\Di\FakeHandleProvider('momo');
return $instance->get();
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyInstanceCompile()
    {
        $container = (new FakeCarModule)->getContainer();
        $dependency = $container->getContainer()['-logo'];
        $code = (new DependencyCompiler($container))->compile($dependency);
        $expected = <<<'EOT'
<?php

return 'momo';
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyObjectInstanceCompile()
    {
        $container = (new FakeCarModule)->getContainer();
        $dependency = new Instance(new FakeEngine());
        $code = (new DependencyCompiler($container))->compile($dependency);
        $expected = <<<'EOT'
<?php

return unserialize('O:17:"Ray\\Di\\FakeEngine":0:{}');
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDomainException()
    {
        $this->setExpectedException(\DomainException::class);
        $code = (new DependencyCompiler(new Container))->compile(new FakeInvalidDependency);
    }
}
