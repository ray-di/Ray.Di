<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Di\Qualifier;

class InjectionPointTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InjectionPointInterface
     */
    private $ip;

    /**
     * @var \ReflectionParameter
     */
    private $parameter;

    protected function setUp()
    {
        $this->parameter = new \ReflectionParameter([FakeWalkRobot::class, '__construct'], 'rightLeg');
        $this->ip = new InjectionPoint($this->parameter, new AnnotationReader);
    }

    public function testGetParameter()
    {
        $actual = $this->ip->getParameter();
        $this->assertSame($this->parameter, $actual);
    }

    public function testGetMethod()
    {
        $actual = $this->ip->getMethod();
        $this->assertSame((string) $this->parameter->getDeclaringFunction(), (string) $actual);
    }

    public function testGetClass()
    {
        $actual = $this->ip->getClass();
        $this->assertSame((string) $this->parameter->getDeclaringClass(), (string) $actual);
    }

    public function testMethodAnnotation()
    {
        /** @var $constant FakeConstant */
        $constant = $this->ip->getMethodAnnotation(FakeConstant::class);
        $this->assertInstanceOf(FakeConstant::class, $constant);
        $this->assertSame(10, $constant->value);
    }

    public function testMethodAnnotations()
    {
        /** @var $constant FakeConstant */
        $annotations = $this->ip->getMethodAnnotation();
        $this->assertInstanceOf(FakeConstant::class, $annotations[0]);
    }

    public function testClassAnnotation()
    {
        /** @var $constant FakeConstant */
        $constant = $this->ip->getClassAnnotation(FakeConstant::class);
        $this->assertInstanceOf(FakeConstant::class, $constant);
        $this->assertSame('class_constant_val', $constant->value);
    }

    public function testClassAnnotations()
    {
        /** @var $constant FakeConstant */
        $annotations = $this->ip->getClassAnnotation();
        $this->assertInstanceOf(FakeConstant::class, $annotations[0]);
    }
}
