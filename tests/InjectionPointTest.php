<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;

class InjectionPointTest extends TestCase
{
    /**
     * @var InjectionPointInterface
     */
    private $ip;

    /**
     * @var ReflectionParameter
     */
    private $parameter;

    protected function setUp() : void
    {
        $this->parameter = new ReflectionParameter([FakeWalkRobot::class, '__construct'], 'rightLeg');
        $this->ip = new InjectionPoint($this->parameter, new AnnotationReader);
    }

    public function testGetParameter() : void
    {
        $actual = $this->ip->getParameter();
        $this->assertSame($this->parameter, $actual);
    }

    public function testGetMethod() : void
    {
        $actual = $this->ip->getMethod();
        $this->assertSame((string) $this->parameter->getDeclaringFunction(), (string) $actual);
    }

    public function testGetClass() : void
    {
        $actual = $this->ip->getClass();
        $this->assertSame((string) $this->parameter->getDeclaringClass(), (string) $actual);
    }

    public function testGetQualifiers() : void
    {
        /* @var $constant FakeConstant */
        $annotations = $this->ip->getQualifiers();
        $this->assertCount(1, $annotations);
        $this->assertInstanceOf(FakeConstant::class, $annotations[0]);
    }
}
