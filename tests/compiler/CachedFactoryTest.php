<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Cache\PhpFileCache;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;
use function spl_object_hash;

class CachedFactoryTest extends TestCase
{
    public function testInstanceCachedInStaticMemory() : void
    {
        $injector1 = $this->getInjector('dev');
        $injector2 = $this->getInjector('dev');
        $this->assertSame(spl_object_hash($injector1), spl_object_hash($injector2));
    }

    public function testInstanceCachedInFileCache() : void
    {
        $injector1 = $this->getInjector('prod');
        $this->assertFalse(DevCache::$wasHit);
        $injector2 = $this->getInjector('prod');
        $this->assertFalse(DevCache::$wasHit);
        $this->assertSame(spl_object_hash($injector1), spl_object_hash($injector2));
        $injector2->getInstance(FakeRobotInterface::class);
    }

    /**
     * @param 'dev'|'prod' $context
     */
    private function getInjector(string $context) : InjectorInterface
    {
        if ($context === 'dev') {
            return CachedInjectorFactory::getInstance(
                'dev',
                __DIR__ . '/tmp/dev',
                function () : AbstractModule {
                    return new FakeToBindPrototypeModule;
                }
            );
        }

        return CachedInjectorFactory::getInstance(
            'prod',
            __DIR__ . '/tmp/prod',
            function () : AbstractModule {
                $module = new FakeToBindSingletonModule;
                $module->install(new DiCompileModule(true));

                return $module;
            },
            new DevCache(new PhpFileCache(__DIR__ . '/tmp/injector_cache')),
            [FakeRobotInterface::class] // FakeRobotInterface object is cached in an injector.
        );
    }
}
