<?php

use Ray\Di\Injector;

require __DIR__ . '/autoload.php';

$injector = Injector::create([new UserModule]);
$user = $injector->getInstance('User');

/* @var $user User */
$user->createUser('Koriym', rand(18,35));
$user->createUser('Bear', rand(18,35));
$user->createUser('Yoshi', rand(18,35));
$users = $user->readUsers();
var_export($users);

// begin Transaction["Koriym",20]
// commit
// begin Transaction["Bear",25]
// commit
// begin Transaction["Yoshi",18]
// commit
// Timer start
// Name:Koriym	Age:20
// Name:Bear	Age:25
// Name:Yoshi	Age:18
// Timer stop:[0.0000741] sec
