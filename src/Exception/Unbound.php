<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

use Exception;
use function get_class;
use LogicException;

class Unbound extends LogicException implements ExceptionInterface
{
    public function __toString()
    {
        $messages = [sprintf("- %s\n", $this->getMessage())];
        $e = $this->getPrevious();
        if (! $e instanceof Exception) {
            return $this->getMainMessage($this);
        }
        if ($e instanceof self) {
            return $this->buildMessage($e, $messages) . "\n" . $e->getTraceAsString();
        }

        return parent::__toString();
    }

    /**
     * @param array<int, string> $msg
     */
    private function buildMessage(self $e, array $msg) : string
    {
        $lastE = $e;
        while ($e instanceof self) {
            $msg[] = sprintf("- %s\n", $e->getMessage());
            $lastE = $e;
            $e = $e->getPrevious();
        }
        array_pop($msg);
        $msg = array_reverse($msg);

        return $this->getMainMessage($lastE) . implode('', $msg);
    }

    private function getMainMessage(self $e) : string
    {
        return sprintf(
            "exception '%s' with message '%s'\n",
            get_class($e),
            $e->getMessage()
        );
    }
}
