<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\UserInterface;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Instance
{
    public $userId;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $age;

    /**
     * @var string
     */
    public $gender;

    /**
     * @param User
     */
    public $user;

    /**
     * @Inject
     * @Named("id")
     *
     * @param string $db
     *
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @Inject
     * @Named("name=user_name,age=user_age,gender=user_gender")
     *
     * @param string $db
     *
     */
    public function setUser($name, $age, $gender="male")
    {
        $this->name = $name;
        $this->age = $age;
        $this->gender = $gender;
    }

    /**
     * @Inject
     * @Named("userId=id")
     *
     * @param string $db
     *
     */
    public function setIdUser($userId, UserInterface $user)
    {
        $this->userId = $userId;
        $this->usr = $user;
    }

}
