<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Koriym\Printo\Printo;
use Ray\Di\Container;
use Ray\Di\Name;

use function assert;
use function class_exists;
use function explode;
use function file_exists;
use function file_put_contents;
use function interface_exists;
use function mkdir;
use function str_replace;

use const LOCK_EX;

final class GraphDumper
{
    /** @var Container */
    private $container;

    /** @var string */
    private $scriptDir;

    public function __construct(Container $container, string $scriptDir)
    {
        $this->container = $container;
        $this->scriptDir = $scriptDir;
    }

    public function __invoke(): void
    {
        $container = $this->container->getContainer();
        foreach ($container as $dependencyIndex => $dependency) {
            $isNotInjector = $dependencyIndex !== 'Ray\Di\InjectorInterface-' . Name::ANY;
            if ($isNotInjector) {
                $this->write((string) $dependencyIndex);
            }
        }
    }

    private function write(string $dependencyIndex): void
    {
        if ($dependencyIndex === 'Ray\Aop\MethodInvocation-') {
            return;
        }

        [$interface, $name] = explode('-', $dependencyIndex);
        assert(class_exists($interface) || interface_exists($interface) || $interface === '');
        $instance = (new ScriptInjector($this->scriptDir))->getInstance($interface, $name);
        $graph = (string) (new Printo($instance))
            ->setRange(Printo::RANGE_ALL)
            ->setLinkDistance(130)
            ->setCharge(-500);
        $graphDir = $this->scriptDir . '/graph/';
        if (! file_exists($graphDir)) {
            mkdir($graphDir);
        }

        $file = $graphDir . str_replace('\\', '_', $dependencyIndex) . '.html';
        file_put_contents($file, $graph, LOCK_EX);
    }
}
