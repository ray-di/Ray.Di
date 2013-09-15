<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 */
namespace Ray\Di\Module\Provider;

use Ray\Aop\Compiler;
use PHPParser_PrettyPrinter_Default;
use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_BuilderFactory;
use Ray\Di\ProviderInterface;

/**
 * Compiler provider for InjectorModule.
 */
class CompilerProvider implements ProviderInterface
{
    public function get()
    {
        return new Compiler(
            sys_get_temp_dir(),
            new PHPParser_PrettyPrinter_Default,
            new PHPParser_Parser(new PHPParser_Lexer),
            new PHPParser_BuilderFactory
        );
    }
}
