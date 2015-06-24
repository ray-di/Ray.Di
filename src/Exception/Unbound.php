<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di\Exception;

use Ray\Di\Exception;

class Unbound extends \LogicException implements ExceptionInterface
{

    public function __toString()
    {
        $messages = [sprintf("- %s\n", $this->getMessage())];
        $e = $this->getPrevious();
        return $this->buildMessage($e, $messages);
    }

    /**
     * @param Unbound $e
     * @param array   $msg
     *
     * @return string
     */
    private function buildMessage(Unbound $e, array $msg)
    {
        while ($e instanceof Unbound) {
            $msg[] = sprintf("- %s\n", $e->getMessage());
            $lastE = $e;
            $e = $e->getPrevious();
        }
        array_pop($msg);
        $msg = array_reverse($msg);

        return $this->getMainMessage($lastE) . implode('', $msg);
    }

    private function getMainMessage(Unbound $e)
    {
        return sprintf(
            "exception '%s' with message '%s'\n",
            get_class($e),
            $e->getMessage()
        );
    }
}
