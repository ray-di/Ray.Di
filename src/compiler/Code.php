<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

final class Code
{
    /**
     * @var bool
     */
    public $isSingleton;

    /**
     * @var null|IpQualifier
     */
    public $qualifiers;

    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node, bool $isSingleton = false, IpQualifier $qualifier = null)
    {
        $this->node = $node;
        $this->isSingleton = $isSingleton;
        $this->qualifiers = $qualifier;
    }

    public function __toString()
    {
        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile([$this->node]);
    }
}
