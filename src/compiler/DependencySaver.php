<?php

declare(strict_types=1);

namespace Ray\Compiler;

final class DependencySaver
{
    /**
     * @var string
     */
    private $scriptDir;

    /**
     * @var FilePutContents
     */
    private $filePutContents;

    public function __construct(string $scriptDir)
    {
        $this->scriptDir = $scriptDir;
        $this->filePutContents = new FilePutContents;
    }

    public function __invoke(string $dependencyIndex, Code $code) : void
    {
        $pearStyleName = \str_replace('\\', '_', $dependencyIndex);
        $instanceScript = \sprintf(ScriptInjector::INSTANCE, $this->scriptDir, $pearStyleName);
        ($this->filePutContents)($instanceScript, (string) $code . PHP_EOL);
        if ($code->qualifiers) {
            $this->saveQualifier($code->qualifiers);
        }
    }

    private function saveQualifier(IpQualifier $qualifer) : void
    {
        $qualifier = $this->scriptDir . '/qualifer';
        ! \file_exists($qualifier) && ! @mkdir($qualifier) && ! is_dir($qualifier);
        $class = $qualifer->param->getDeclaringClass();
        if (! $class instanceof \ReflectionClass) {
            throw new \LogicException; // @codeCoverageIgnore
        }
        $fileName = \sprintf(
            ScriptInjector::QUALIFIER,
            $this->scriptDir,
            \str_replace('\\', '_', $class->name),
            $qualifer->param->getDeclaringFunction()->name,
            $qualifer->param->name
        );
        ($this->filePutContents)($fileName, \serialize($qualifer->qualifier) . PHP_EOL);
    }
}
