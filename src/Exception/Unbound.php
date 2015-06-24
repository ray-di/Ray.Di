<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di\Exception;


class Unbound extends \LogicException implements ExceptionInterface
{

    public function __toString()
    {
        $msg = sprintf("-%s\n", $this->getMessage());
        $e = $this->getPrevious();
        $msg = $this->buildMessage($e, $msg);

        return $msg;
    }

    /**
     * @param Unbound $e
     * @param string  $msg
     *
     * @return string
     */
    private function buildMessage(Unbound $e, $msg)
    {
        while ($e instanceof Unbound) {
            if ($e instanceof Untargetted) {
                $msg = $e->getMessage() . PHP_EOL . $msg;
                break;
            }
            $msg .= sprintf("-%s\n", $e->getMessage());
            $e = $e->getPrevious();
        }

        return $msg;
    }
}
