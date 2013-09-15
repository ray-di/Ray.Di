<?php

use Ray\Di\Injector;

require __DIR__ . '/src.php';

$pdo = new \PDO('sqlite::memory:', null, null);
$user = new User($pdo);
$user->init();
$user->createUser('Koriym', rand(18,35));
$user->createUser('Bear', rand(18,35));
$user->createUser('Yoshi', rand(18,35));
$users = $user->readUsers();
var_export($users);
