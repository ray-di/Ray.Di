<?php
include dirname(__DIR__) . '/tests/Definition/Full.php';
$di = include dirname(__DIR__) . '/scripts/instance.php';
$config = $di->getContainer()->getForge()->getConfig();
var_dump($config->fetch('\Ray\Di\Definition\Full'));

/**
array(3) {
    [0]=>
    array(0) {
    }
    [1]=>
    array(0) {
    }
    [2]=>
    array(3) {
        ["Scope"]=>
        string(9) "singleton"
        ["Inject"]=>
        array(1) {
            ["setter"]=>
            array(2) {
                [0]=>
                array(1) {
                    ["setId"]=>
                    array(1) {
                        [0]=>
                        array(5) {
                            ["pos"]=>
                            int(0)
                            ["typehint"]=>
                            string(0) ""
                            ["name"]=>
                            string(2) "id"
                            ["annotate"]=>
                            string(2) "id"
                            ["typehint_by"]=>
                            array(0) {
                            }
                        }
                    }
                }
                [1]=>
                array(1) {
                    ["setUser"]=>
                    array(3) {
                        [0]=>
                        array(5) {
                            ["pos"]=>
                            int(0)
                            ["typehint"]=>
                            string(0) ""
                            ["name"]=>
                            string(4) "name"
                            ["annotate"]=>
                            string(9) "user_name"
                            ["typehint_by"]=>
                            array(0) {
                            }
                        }
                        [1]=>
                        array(5) {
                            ["pos"]=>
                            int(1)
                            ["typehint"]=>
                            string(0) ""
                            ["name"]=>
                            string(3) "age"
                            ["annotate"]=>
                            string(8) "user_age"
                            ["typehint_by"]=>
                            array(0) {
                            }
                        }
                        [2]=>
                        array(5) {
                            ["pos"]=>
                            int(2)
                            ["typehint"]=>
                            string(0) ""
                            ["name"]=>
                            string(6) "gender"
                            ["annotate"]=>
                            string(11) "user_gender"
                            ["typehint_by"]=>
                            array(0) {
                            }
                        }
                    }
                }
            }
        }
        ["user"]=>
        array(6) {
            ["Cache"]=>
            array(1) {
                ["time=10"]=>
                string(5) "onGet"
            }
            ["Template"]=>
            array(1) {
                [""]=>
                string(5) "onGet"
            }
            ["Validation"]=>
            array(1) {
                [""]=>
                string(5) "onGet"
            }
            ["Log"]=>
            array(1) {
                [""]=>
                string(5) "onGet"
            }
            ["Pull"]=>
            array(1) {
                ["app:self//user"]=>
                string(5) "onGet"
            }
            ["Provide"]=>
            array(2) {
                ["id"]=>
                string(9) "provideId"
                ["order"]=>
                string(12) "provideOrder"
            }
        }
    }
}
