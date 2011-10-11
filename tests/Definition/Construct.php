<?php

namespace Aura\Di\Definition;

use Aura\Di\Mock\DbInterface,
    Aura\Di\Mock\UserInterface;

/**
 * Constructor Injection
 *
 */
class Construct
{
    /**
     * @var DbInterface
     */
    public $db;

    /**
     * @Inject
     *
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
}