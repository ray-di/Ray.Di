<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Definition;

/**
 * Test class for Config.
 */
class ApcConfigTest extends ConfigTest
{
    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->config = new ApcConfig(new Annotation(new Definition, new AnnotationReader));;
    }

    /**
     * coverage for the "merged already" portion of the fetch() method
     */
    public function testFetchTwiceForMerge()
    {
        $config1 = $this->config->fetch('Ray\Di\MockParentClass');
        $config2 = $this->config->fetch('Ray\Di\MockParentClass');
        $definition1 = (array) ($config1[2]);
        $definition2 = (array) ($config2[2]);
        $this->assertSame($definition1, $definition2);
    }

    public function testConfigRetainDefintionAfterFetch()
    {
        $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
        $def = $this->config->getDefinition();
        $definition = $def['Ray\Di\Definition\MockDefinitionClass'];
        $this->assertTrue($definition instanceof Definition);
    }

    /**
     * @expectedException Ray\Di\Exception\Configuration
     */
    public function testConfigInvalidClass()
    {
        $this->config->fetch('XXXXX');
    }
}
