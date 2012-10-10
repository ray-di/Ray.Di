<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
use Ray\Di\Di\Named;

/**
 * @Scope("singleton")
 */
class Full
{
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
     */
    public function setUser($name, $age, $gender="male")
    {
        $this->name = $name;
        $this->age = $age;
        $this->gender = $gender;
    }

    /**
     * @Cache("time=10")
     * @Template
     * @Validation
     * @Log
     * @Pull("app:self//user")
     */
    public function onGet($id, $order)
    {}

    /**
     * @Provide("id")
     */
    public function provideId()
    {
    }

    /**
     * @Provide("order")
     */
    public function provideOrder()
    {
    }

}
