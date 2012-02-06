<?php
/**
 * Ray
 *
 * This file is taken from Aura Project and modified. (namespace only)
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;

/**
 * Matcher
 *
 * @package Aura.Di
 *
 */
class Matcher
{
    const ANY = true;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     *
     * Invokes the closure to create the instance.
     *
     * @return object The object created by the closure.
     *
     */
    public function __invoke($arg)
    {
        $callable = $this->callable;
        return $callable($arg);
    }

    /**
     * Any match
     *
     * @return Ray\Di\Matcher
     */
    public function any()
    {
        return function(){
            return self::ANY;
        };
    }

    /**
     * Match binding annotation
     *
     * @param string $annotationName
     *
     * @return \Ray\Di\Matcher
     */
    public function annotatedWith($annotationName)
    {
        $reader = $this->reader;
        $this->callable = function($class) use ($annotationName, $reader) {
            $methods = (new \ReflectionClass($class))->getMethods();
            $result = [];
            foreach ($methods as $method) {
                $annotation = $reader->getMethodAnnotation($method, $annotationName);
                if ($annotation) {
                    $matched = new Matched;
                    $matched->methodName = $method->name;
                    $matched->annotation = $annotation;
                    $result[] = $matched;
                }
            }
            return $result;
        };
        return $this;
    }
}
