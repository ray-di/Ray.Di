<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

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
        $this->annotationSacnner = new Annotation(new Definition, new AnnotationReader);
    }

    public function testgetDefinitionScope()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = 'prototype';
        $this->assertSame($expected, $definition['Scope']);
    }

    public function testgetDefinitionPostConstruct()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = 'onInit';
        $this->assertSame($expected, $definition['PostConstruct']);
    }

    public function testgetDefinitionPreDestroy()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = 'onEnd';
        $this->assertSame($expected, $definition['PreDestroy']);
    }

    public function testgetDefinitionInjectConstruct()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = [
            '__construct' => [
                [
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Aura\\Di\\ForgeInterface',
                    Definition::PARAM_NAME => 'forge',
                    Definition::PARAM_ANNOTATE => Definition::NAME_UNSPECIFIED,
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ],
                [
                    Definition::PARAM_POS => 1,
                    Definition::PARAM_TYPEHINT => '',
                    Definition::PARAM_NAME => 'id',
                    Definition::PARAM_ANNOTATE => 'usr_id',
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ]
            ]
        ];
        $actual = $definition['Inject'][Definition::INJECT_SETTER];
        $this->assertContains($expected, $actual);
    }

    public function testgetDefinitionInjectMethod0()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = [
            'setDb' => [
                [
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Ray\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => Definition::NAME_UNSPECIFIED,
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ],
            ],
        ];
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    public function testgetDefinitionInjectMethodSetUserDb()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = [
            'setUserDb' => [
                [
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Ray\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'user_db',
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ],
            ],
        ];
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    public function testgetDefinitionInjectMethodSetAdminDb()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = [
            'setAdminDb' => [
                [
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Ray\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'stage_db',
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ],
            ],
        ];
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    public function testgetDefinitionInjectMethodSetDouble()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $expected = [
            'setDouble' => [
                [
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Ray\Di\Mock\UserInterface',
                    Definition::PARAM_NAME => 'user',
                    Definition::PARAM_ANNOTATE => 'admin_user',
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ],
                [
                    Definition::PARAM_POS => 1,
                    Definition::PARAM_TYPEHINT => 'Ray\Di\Mock\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'production_db',
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ]
            ]
        ];
        $actual = $definition['Inject'][Definition::INJECT_SETTER];
        $this->assertContains($expected, $actual);
   }

    /**
     * @expectedException Ray\Di\Exception\MultipleAnnotationNotAllowed
     */
    public function testMultipleAnnotationNotAllowedExcection()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionMultiplePostConstructClass');
    }

    public function testSingleVarAnnotattion()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockNamed');
        $expected = [
            'setUserDb' => [
                [
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Ray\\Di\\Mock\\DbInterface',
                    Definition::PARAM_NAME => 'db',
                    Definition::PARAM_ANNOTATE => 'user_db',
                    Definition::PARAM_TYPEHINT_BY => [],
                    Definition::OPTIONAL => false
                ],
            ],
        ];
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    /**
     * 	@expectedException Ray\Di\Exception\Named
     */
    public function testInvalidNamed()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\InvalidNamed');
    }

    public function testImplementedBy()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Mock\LogInterface');
        $expected ='Ray\Di\Mock\Log';
        $this->assertSame($expected, $definition['ImplementedBy']);
    }

    public function testImplementedByTwice()
    {
        $definition1 = $this->annotationSacnner->getDefinition('Ray\Di\Mock\LogInterface');
        // to be cached
        $definition2 = $this->annotationSacnner->getDefinition('Ray\Di\Mock\LogInterface');
        $this->assertSame($definition1, $definition2);
    }

    public function testImplemetedBy()
    {
        $definition = $this->annotationSacnner->getDefinition('Ray\Di\Definition\Implemented');
        $expected = [
            'setLog' => [
                [
                    Definition::PARAM_POS => 0,
                    Definition::PARAM_TYPEHINT => 'Ray\\Di\\Mock\\LogInterface',
                    Definition::PARAM_NAME => 'log',
                    Definition::PARAM_ANNOTATE => Definition::NAME_UNSPECIFIED,
                    Definition::PARAM_TYPEHINT_BY => [Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, 'Ray\Di\Mock\Log'],
                    Definition::OPTIONAL => false
                ],
            ],
        ];
        $this->assertContains($expected, $definition['Inject'][Definition::INJECT_SETTER]);
    }

    public function testgetDefinitionDobule()
    {
        $definition1 = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $definition2 = $this->annotationSacnner->getDefinition('Ray\Di\Definition\MockDefinitionClass');
        $this->assertSame($definition1, $definition2);
    }

    /**
     * @expectedException Ray\Di\Exception\NotReadable
     */
    public function testNotReadable()
    {
        $this->annotationSacnner->getDefinition('NotReadable');
    }
}
