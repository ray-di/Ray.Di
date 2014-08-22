<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Module\Provider;

use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Compiler;
use Ray\Di\ProviderInterface;

/**
 * Compiler provider for InjectorModule.
 */
class CompilerProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return Compiler
     */
    public function get()
    {
        return new Compiler(
            sys_get_temp_dir(),
            new PHPParser_PrettyPrinter_Default
        );
    }
}
