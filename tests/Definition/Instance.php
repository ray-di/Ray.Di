<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\UserInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Instance
{
    /**
     * @var int
     */
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
     * @param array
     */
    public $userFavorites;

    /**
     * @param $id
     *
     * @Inject
     * @Named("id")
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param        $name
     * @param        $age
     * @param string $gender
     *
     * @Inject
     * @Named("name=user_name,age=user_age,gender=user_gender")
     */
    public function setUser($name, $age, $gender="male")
    {
        $this->name = $name;
        $this->age = $age;
        $this->gender = $gender;
    }

    /**
     * @param                            $userId
     * @param \Ray\Di\Mock\UserInterface $user
     *
     * @Inject
     * @Named("userId=id")
     */
    public function setIdUser($userId, UserInterface $user)
    {
        $this->userId = $userId;
        $this->usr = $user;
    }

    /**
     * @param array $userFavorites
     *
     * @Inject
     * @Named("userFavorites=user_favorites")
     */
    public function setUserFavorites(array $userFavorites)
    {
        $this->userFavorites = $userFavorites;
    }
}
