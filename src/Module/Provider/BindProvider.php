<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Module\Provider;

use Ray\Di\ProviderInterface;
use Ray\Aop\Bind;

class BindProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return Bind
     */
    public function get()
    {
        return new Bind;
    }
}
