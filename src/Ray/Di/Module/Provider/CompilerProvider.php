<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Module\Provider;

use Ray\Aop\Compiler;
use PHPParser_PrettyPrinter_Default;
use Ray\Di\ProviderInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Compiler provider for InjectorModule
 */
class CompilerProvider implements ProviderInterface
{
    /**
     * @var string
     *
     * @Inject
     * @Named("aop_dir")
     */
    private $aopDir;

    public function __construct($aopDir = null)
    {
        $this->aopDir ? $aopDir : sys_get_temp_dir();
    }

    /**
     * {@inheritdoc}
     *
     * @return object|Compiler
     */
    public function get()
    {
        return new Compiler(
            $this->aopDir,
            new PHPParser_PrettyPrinter_Default
        );
    }
}
