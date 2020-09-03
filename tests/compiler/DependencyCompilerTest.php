<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PHPUnit\Framework\TestCase;
use Ray\Di\Container;
use Ray\Di\Dependency;
use Ray\Di\Instance;
use Ray\Di\Name;

class DependencyCompilerTest extends TestCase
{
    /**
     * @var Dependency
     */
    private $dependency;

    protected function setUp() : void
    {
        parent::setUp();
        delete_dir($_ENV['TMP_DIR']);
    }

    public function testInstanceCompileString() : void
    {
        $dependencyInstance = new Instance('bear');
        $code = (new DependencyCode(new Container))->getCode($dependencyInstance);
        $expected = <<<'EOT'
<?php

return 'bear';
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testInstanceCompileInt() : void
    {
        $dependencyInstance = new Instance(1);
        $code = (new DependencyCode(new Container))->getCode($dependencyInstance);
        $expected = <<<'EOT'
<?php

return 1;
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testInstanceCompileArray() : void
    {
        $dependencyInstance = new Instance([1, 2, 3]);
        $code = (new DependencyCode(new Container))->getCode($dependencyInstance);
        $expected = <<<'EOT'
<?php

return array(1, 2, 3);
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyCompile() : void
    {
        $container = (new FakeCarModule)->getContainer();
        $dependency = $container->getContainer()['Ray\Compiler\FakeCarInterface-' . Name::ANY];
        $code = (new DependencyCode($container))->getCode($dependency);
        $expectedTemplate = <<<'EOT'
<?php

namespace Ray\Di\Compiler;

$instance = new \Ray\Compiler\FakeCar($prototype('Ray\\Compiler\\FakeEngineInterface-{ANY}'));
$instance->setTires($prototype('Ray\\Compiler\\FakeTyreInterface-{ANY}'), $prototype('Ray\\Compiler\\FakeTyreInterface-{ANY}'), null);
$instance->setHardtop($prototype('Ray\\Compiler\\FakeHardtopInterface-{ANY}'));
$instance->setMirrors($singleton('Ray\\Compiler\\FakeMirrorInterface-right'), $singleton('Ray\\Compiler\\FakeMirrorInterface-left'));
$instance->setSpareMirror($singleton('Ray\\Compiler\\FakeMirrorInterface-right'));
$instance->setHandle($prototype('Ray\\Compiler\\FakeHandleInterface-{ANY}', array('Ray\\Compiler\\FakeCar', 'setHandle', 'handle')));
$instance->postConstruct();
$is_singleton = false;
return $instance;
EOT;
        $expected = \str_replace('{ANY}', Name::ANY, $expectedTemplate);
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyProviderCompile() : void
    {
        $container = (new FakeCarModule())->getContainer();
        $dependency = $container->getContainer()['Ray\Compiler\FakeHandleInterface-' . Name::ANY];
        $code = (new DependencyCode($container))->getCode($dependency);
        $expected = <<<'EOT'
<?php

namespace Ray\Di\Compiler;

$instance = new \Ray\Compiler\FakeHandleProvider('momo');
$is_singleton = false;
return $instance->get();
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyInstanceCompile() : void
    {
        $container = (new FakeCarModule)->getContainer();
        $dependency = $container->getContainer()['-logo'];
        $code = (new DependencyCode($container))->getCode($dependency);
        $expected = <<<'EOT'
<?php

return 'momo';
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDependencyObjectInstanceCompile() : void
    {
        $container = (new FakeCarModule)->getContainer();
        $dependency = new Instance(new FakeEngine());
        $code = (new DependencyCode($container))->getCode($dependency);
        $expected = <<<'EOT'
<?php

return unserialize('O:23:"Ray\\Compiler\\FakeEngine":0:{}');
EOT;
        $this->assertSame($expected, (string) $code);
    }

    public function testDomainException() : void
    {
        $this->expectException(\DomainException::class);
        (new DependencyCode(new Container))->getCode(new FakeInvalidDependency);
    }

    public function testContextualProviderCompile() : void
    {
        $container = (new FakeContextualModule('context'))->getContainer();
        $dependency = $container->getContainer()['Ray\Compiler\FakeRobotInterface-' . Name::ANY];
        $code = (new DependencyCode($container))->getCode($dependency);
        $expected = <<<'EOT'
<?php

namespace Ray\Di\Compiler;

$instance = new \Ray\Compiler\FakeContextualProvider();
$instance->setContext('context');
$is_singleton = false;
return $instance->get();
EOT;
        $this->assertSame($expected, (string) $code);
    }
}
