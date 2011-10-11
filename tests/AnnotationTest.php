<?php

namespace Aura\Di;

/**
 * Test class for Annotation.
 */
class AnnotationTest extends \PHPUnit_Framework_TestCase
{
    protected $annotationSacnner;

    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->annotationSacnner = new Annotation();
        $this->annotationSacnner->setConfig(new Config);
    }

    public function testgetDefinitionScope()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = 'prototype';
        $this->assertSame($expected, $definition['Scope']);
    }

    public function testgetDefinitionPostConstruct()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = 'onInit';
        $this->assertSame($expected, $definition['PostConstruct']);
    }

    public function testgetDefinitionPreDestoroy()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = 'onEnd';
        $this->assertSame($expected, $definition['PreDestoroy']);
    }

    public function testgetDefinitionInjectConstruct()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = array(
            '__construct' => array(
                array(
                        Definition::PARAM_POS => 0,
                        Definition::PARAM_TYPEHINT => 'Aura\\Di\\ForgeInterface',
                        Definition::PARAM_NAME => 'forge',
                        Definition::PARAM_ANNOTATE => Definition::NAME_UNSPECIFIED,
                        Definition::PARAM_TYPEHINT_BY => array()
        ),
                array(
                    Definition::PARAM_POS => 1,
                    Definition::PARAM_TYPEHINT => '',
                    Definition::PARAM_NAME => 'id',
                    Definition::PARAM_ANNOTATE => 'usr_id',
                    Definition::PARAM_TYPEHINT_BY => array()
        )
            )
        );
        $actual = $definition['Inject'][Definition::INJECT_SETTER];
        $this->assertContains($expected, $actual);
    }

    public function testgetDefinitionInjectMethod0()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = array(
            'setDb' => array(
                array(
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Aura\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => Definition::NAME_UNSPECIFIED,
                    Definition::PARAM_TYPEHINT_BY => array()
        ),
            ),
        );
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    public function testgetDefinitionInjectMethodSetUserDb()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = array(
            'setUserDb' => array(
                array(
        			Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Aura\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'user_db',
                    Definition::PARAM_TYPEHINT_BY => array()
        ),
            ),
        );
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    public function testgetDefinitionInjectMethodSetAdminDb()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = array(
            'setAdminDb' => array(
                array(
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Aura\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'staege_db',
                    Definition::PARAM_TYPEHINT_BY => array()
        ),
            ),
        );
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    public function testgetDefinitionInjectMethodSetDouble()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionClass');
        $expected = array(
        	'setDouble' => array(
                array (
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Aura\Di\Mock\UserInterface',
                    Definition::PARAM_NAME => 'user',
                    Definition::PARAM_ANNOTATE => 'admin_user',
                    Definition::PARAM_TYPEHINT_BY => array()
        ),
                array (
                    Definition::PARAM_POS => 1,
                    Definition::PARAM_TYPEHINT => 'Aura\Di\Mock\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'production_db',
                    Definition::PARAM_TYPEHINT_BY => array()
        )
            )
        );
        $actual = $definition['Inject'][Definition::INJECT_SETTER];
        $this->assertContains($expected, $actual);
   }

    /**
     * @expectedException Aura\Di\Exception\MultipleAnnotationNotAllowed
     */
    public function testMultipleAnnotationNotAllowedExcection()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\MockDefinitionMultiplePostConstructClass');
    }

    public function testSingleVarAnnotattion()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\Named');
        $expected = array(
            'setUserDb' => array(
                array(
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Aura\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'user_db',
                    Definition::PARAM_TYPEHINT_BY => array()
                ),
            ),
        );
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    /**
     * @expectedException Aura\Di\Exception\InvalidNamed
     */
    public function testInvalidNamed()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\InvalidNamed');
    }

    public function testImplementedBy()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Mock\LogInterface');
        $expected = array('ImplementedBy' => 'Aura\Di\Mock\Log');
        $actual = $definition;
        $this->assertSame($expected, $definition);
    }

    public function testImplementedByTwice()
    {
        $definition1 = $this->annotationSacnner->getDefinition('Aura\Di\Mock\LogInterface');
        // to be cached
        $definition2 = $this->annotationSacnner->getDefinition('Aura\Di\Mock\LogInterface');
        $this->assertSame($definition1, $definition2);
    }

    public function testImplemetedBy()
    {
        $definition = $this->annotationSacnner->getDefinition('Aura\Di\Definition\Implemented');
        $expected = array(
            'setLog' => array(
                array(
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Aura\\Di\\Mock\\LogInterface',
                    Definition::PARAM_NAME => 'log',
                    Definition::PARAM_ANNOTATE => Definition::NAME_UNSPECIFIED,
                    Definition::PARAM_TYPEHINT_BY => array(Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, 'Aura\Di\Mock\Log')
                ),
            ),
        );
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }
}