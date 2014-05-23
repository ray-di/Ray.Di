<?php

namespace Ray\Di\Mock;

class SingletonInterceptorConsumer2
{
    public $db;

    /**
     * Set by interceptor
     */
    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }

    public function getDb()
    {
        return $this->db;
    }
}
