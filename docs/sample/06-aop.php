<?php

namespace Ray\Di\Sample;

use Ray\Di\AbstractModule;
use Ray\Di\Exception;
use Ray\Di\Injector;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

/**
 * NotOnWeekends
 *
 * @Annotation
 * @Target("METHOD")
 */
final class NotOnWeekends
{
}

class Billing
{
    /**
     * @NotOnWeekends
     */
    public function chargeOrder()
    {
        return 'charged';
    }
}

class WeekendBlocker implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        if (getdate()['weekday'][0] === 'S') {
            throw new \RuntimeException(
                $invocation->getMethod()->getName() . " not allowed on weekends!"
            );
        }

        return $invocation->proceed();
    }
}

class WeekendModule extends AbstractModule
{

    protected function configure()
    {
        $this->bindInterceptor(
             $this->matcher->any(), // class matching
             $this->matcher->annotatedWith('\Ray\Di\Sample\NotOnWeekends'), // method matching
             [new WeekendBlocker] // interceptors
        );
    }
}


$injector = Injector::create([new WeekendModule]);
$billing = $injector->getInstance('Ray\Di\Sample\Billing');
/** @var $robot \Ray\Di\Sample\Billing */

try {
    $result = $billing->chargeOrder();
} catch (\RuntimeException $e) {
    $result = 'blocked';
}

$works = ($result === 'charged' || $result === 'blocked');
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
