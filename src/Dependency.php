<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind as AopBind;
use Ray\Aop\Compiler as AopCompiler;
use Ray\Aop\Pointcut;

final class Dependency implements DependencyInterface
{
    /**
     * @var NewInstance
     */
    private $newInstance;

    /**
     * @var string
     */
    private $postConstruct;

    /**
     * @var bool
     */
    private $isSingleton = false;

    /**
     * @var mixed
     */
    private $instance;

    /**
     * @param NewInstance       $newInstance
     * @param \ReflectionMethod $postConstruct
     */
    public function __construct(NewInstance $newInstance, \ReflectionMethod $postConstruct = null)
    {
        $this->newInstance = $newInstance;
        $this->postConstruct = $postConstruct ? $postConstruct->name : null;
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind)
    {
        $index = (string) $bind;
        $container[$index] = $bind->getBound();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(Container $container)
    {
        // singleton ?
        if ($this->isSingleton === true && $this->instance) {
            return $this->instance;
        }

        // create dependency injected instance
        $this->instance = $this->newInstance->__invoke($container);

        // @PostConstruct
        if ($this->postConstruct) {
            $this->instance->{$this->postConstruct}();
        }

        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        if ($scope === Scope::SINGLETON) {
            $this->isSingleton = true;
        }
    }

    /**
     * @param AopCompiler $compiler
     * @param Pointcut[]  $pointcuts
     */
    public function weaveAspects(AopCompiler $compiler, array $pointcuts)
    {
        $class = (string) $this->newInstance;
        $isInterceptor = (new \ReflectionClass($class))->implementsInterface('Ray\Aop\MethodInterceptor');
        if ($isInterceptor) {
            return;
        }
        $bind = new AopBind;
        $bind->bind((string) $this->newInstance, $pointcuts);
        if (! $bind->getBindings()) {
            return ;
        }
        $class = $compiler->compile((string) $this->newInstance, $bind);
        $this->newInstance->weaveAspects($class, $bind);
    }

    public function __sleep()
    {
        return ['newInstance', 'postConstruct', 'isSingleton'];
    }
}
