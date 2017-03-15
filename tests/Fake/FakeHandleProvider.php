<?php
namespace Ray\Di;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class FakeHandleProvider implements ProviderInterface
{
    private $logo;

    /**
     * @Inject
     * @Named("logo")
     */
    public function __construct($logo = 'nardi')
    {
        $this->logo = $logo;
    }

    public function get()
    {
        $handle = new FakeHandle;
        $handle->logo = $this->logo;

        return $handle;
    }
}
