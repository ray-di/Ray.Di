<?php
namespace Ray\Di\Sample;

require __DIR__ . '/User.php';

$pdo = new \PDO('sqlite::memory:', null, null);
$user = new \Ray\Di\Sample\User($pdo);
$user->init();
$user->createUser('Koriym', rand(18,35));
$user->createUser('Bear', rand(18,35));
$user->createUser('Yoshi', rand(18,35));
$users = $user->readUsers();
var_export($users);
