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

    public function testGetQualifiers()
    {
        /** @var $constant FakeConstant */
        $annotations = $this->ip->getQualifiers();
        $this->assertSame(1, count($annotations));
        $this->assertInstanceOf(FakeConstant::class, $annotations[0]);
    }
}
