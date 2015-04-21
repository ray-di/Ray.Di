<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use PhpParser\Node;
use PhpParser\Node\Expr;

final class DependencyCompile
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function __toString()
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        $classCode = $prettyPrinter->prettyPrintFile([$this->node]);

        return $classCode;
    }
}
