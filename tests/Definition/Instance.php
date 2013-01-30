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
    public $usr;

    /**
     * @Inject
     * @Named("id")
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @Inject
     * @Named("name=user_name,age=user_age,gender=user_gender")
     *
     * @param        $name
     * @param        $age
     * @param string $gender
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
     * @param                            $userId
     * @param \Ray\Di\Mock\UserInterface $user
     */
    public function setIdUser($userId, UserInterface $user)
    {
        $this->userId = $userId;
        $this->usr = $user;
    }

}
